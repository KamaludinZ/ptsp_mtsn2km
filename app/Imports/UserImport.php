<?php

namespace App\Imports;

use App\Models\User;
use App\Services\FrontDeskService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Spatie\Permission\Models\Role;

/**
 * Import akun masal dari Excel/CSV (kolom seperti template di daftar Pengguna).
 * Setiap baris yang valid menjadi akun aktif dengan kata sandi acak yang tidak
 * dikirim ke siapa pun dan wajib diganti; baris yang tidak valid dilewati dan
 * dicatat beserta alasannya.
 */
class UserImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    /** Hanya sheet pertama ("Akun"); sheet "Petunjuk" di template tidak dibaca. */
    public function sheets(): array
    {
        return [0 => $this];
    }

    /** Akun yang berhasil dibuat. @var array<int, User> */
    public array $created = [];

    /** Baris gagal: nomor baris di berkas, email, dan alasan. @var array<int, array{row: int, email: ?string, reason: string}> */
    public array $failed = [];

    /** Kolom yang harus ada di baris judul. */
    public const REQUIRED_COLUMNS = ['nama', 'email', 'tipe_pengguna'];

    /** Batas baris akun per berkas agar satu unggahan tetap cepat diproses. */
    public const MAX_ROWS = 1000;

    public function collection(Collection $rows): void
    {
        $missing = array_diff(self::REQUIRED_COLUMNS, array_keys($rows->first()?->all() ?? []));
        if ($rows->isNotEmpty() && $missing) {
            $this->failed[] = ['row' => 1, 'email' => null, 'reason' => 'Kolom ' . implode(', ', $missing) . ' tidak ditemukan; gunakan judul kolom seperti template.'];

            return;
        }

        $roles = Role::where('guard_name', 'web')->pluck('name')->all();
        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // baris 1 berisi judul kolom

            if ($index >= self::MAX_ROWS) {
                $this->failed[] = ['row' => $line, 'email' => null, 'reason' => 'Melebihi ' . self::MAX_ROWS . ' baris per berkas; baris ini dan setelahnya tidak diproses. Unggah sisanya di berkas terpisah.'];

                return;
            }
            $data = $this->normalize($row->all());

            if (! array_filter($data)) {
                continue; // baris kosong
            }

            $reason = $this->rejectReason($data, $roles, $seen);
            if ($reason) {
                $this->failed[] = ['row' => $line, 'email' => $data['email'], 'reason' => $reason];

                continue;
            }

            $seen[] = $data['email'];
            $this->created[] = DB::transaction(fn () => $this->createUser($data));
        }
    }

    /** Ringkasan untuk modal hasil import. */
    public function report(): array
    {
        return ['created' => count($this->created), 'failed' => $this->failed];
    }

    /** @return array{nama: ?string, email: ?string, nomor_whatsapp: ?string, tipe_pengguna: ?string, kode_registrasi: ?string, role: array<int, string>} */
    private function normalize(array $row): array
    {
        $text = fn (string $key) => filled($value = trim((string) ($row[$key] ?? ''))) ? $value : null;
        $phone = $text('nomor_whatsapp');

        return [
            'nama' => $text('nama'),
            'email' => ($email = $text('email')) ? Str::lower($email) : null,
            // Excel menyimpan 0812… sebagai angka 812…: kembalikan nol di depannya.
            'nomor_whatsapp' => $phone ? preg_replace('/^8/', '08', preg_replace('/[^0-9+]/', '', $phone)) : null,
            'tipe_pengguna' => ($type = $text('tipe_pengguna')) ? Str::lower($type) : null,
            'kode_registrasi' => $text('kode_registrasi'),
            // Beberapa role boleh dipisah koma.
            'role' => collect(explode(',', (string) $text('role')))->map(fn ($r) => Str::lower(trim($r)))->filter()->unique()->values()->all(),
        ];
    }

    private function rejectReason(array $data, array $roles, array $seen): ?string
    {
        $validator = Validator::make($data, [
            'nama' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nomor_whatsapp' => ['nullable', 'regex:/^(\+?62|0)8[0-9]{6,14}$/', 'max:20'],
            'tipe_pengguna' => ['required', 'in:' . implode(',', array_keys(FrontDeskService::APPLICANT_TYPES))],
            'kode_registrasi' => ['nullable', 'max:255'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama terlalu panjang.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang.',
            'nomor_whatsapp.regex' => 'Format nomor WhatsApp tidak valid.',
            'nomor_whatsapp.max' => 'Format nomor WhatsApp tidak valid.',
            'tipe_pengguna.required' => 'Tipe pengguna wajib diisi.',
            'tipe_pengguna.in' => 'Tipe pengguna "' . $data['tipe_pengguna'] . '" tidak dikenal.',
            'kode_registrasi.max' => 'Kode registrasi terlalu panjang.',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (in_array($data['email'], $seen, true) || User::where('email', $data['email'])->exists()) {
            return 'Email sudah terdaftar.';
        }

        $unknown = array_diff($data['role'], $roles);
        if ($unknown) {
            return 'Role "' . implode(', ', $unknown) . '" tidak dikenal.';
        }

        return null;
    }

    private function createUser(array $data): User
    {
        $user = new User([
            'name' => $data['nama'],
            'email' => $data['email'],
            'whatsapp_number' => $data['nomor_whatsapp'],
            // Hanya untuk membuat akun: tidak disimpan di tempat lain dan tidak dikirim.
            'password' => Str::password(24),
            'user_type' => $data['tipe_pengguna'],
            'registration_code' => $data['kode_registrasi'],
            'is_active' => true,
        ]);
        // Dibuat oleh admin: tanpa verifikasi email; kata sandi wajib diganti saat pertama masuk.
        $user->forceFill(['email_verified_at' => now(), 'password_changed_at' => now(), 'must_change_password' => true])->save();

        // Tanpa kolom role, akun mengikuti tipe penggunanya seperti saat mendaftar sendiri.
        $roles = $data['role'] ?: array_filter([$data['tipe_pengguna']], fn ($r) => Role::where('guard_name', 'web')->where('name', $r)->exists());
        $user->syncRoles($roles);

        return $user;
    }
}
