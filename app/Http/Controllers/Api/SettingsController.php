<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\Formats;
use App\Support\SettingsStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pengaturan Aplikasi for administrators (/api/pengaturan): the same
 * settings as the Pengaturan screen, saved through SettingsStore.
 */
class SettingsController extends Controller
{
    /** Profil instansi: API field => setting key. */
    private const PROFILE = [
        'nama' => 'app_name',
        'nama_lengkap' => 'app_name_full',
        'slogan' => 'app_tagline',
        'npsn' => 'institution_npsn',
        'nsm' => 'institution_nsm',
        'kepala_madrasah' => 'headmaster_name',
        'nip_kepala_madrasah' => 'headmaster_nip',
        'alamat' => 'contact_address',
        'telepon' => 'contact_phone',
        'email' => 'contact_email',
        'situs' => 'contact_website',
        'whatsapp_ptsp' => 'contact_whatsapp_ptsp',
        'whatsapp_pengaduan' => 'contact_whatsapp_pengaduan',
        'jam_senin_kamis' => 'operating_hours_weekday',
        'jam_jumat' => 'operating_hours_friday',
        'jam_sabtu_minggu' => 'operating_hours_weekend',
    ];

    /** Pengaturan umum (format & penomoran, kop surat, situs, tautan): API field => setting key. */
    private const GENERAL = [
        'awalan_tiket' => 'ticket_prefix',
        'kode_satker_surat' => 'surat_kode_satker',
        'format_tanggal' => 'document_date_format',
        'kop_baris_1' => 'letterhead_line_1',
        'kop_baris_2' => 'letterhead_line_2',
        'deskripsi_footer' => 'footer_description',
        'hak_cipta' => 'copyright_text',
        'mode_perawatan' => 'enable_maintenance',
        'pesan_perawatan' => 'maintenance_message',
        'facebook' => 'social_facebook',
        'instagram' => 'social_instagram',
        'youtube' => 'social_youtube',
        'twitter' => 'social_twitter',
        'tautan_kemenag' => 'link_kemenag',
        'tautan_kanwil' => 'link_kanwil',
        'tautan_kankemenag' => 'link_kankemenag',
    ];

    /** GET /api/pengaturan/profil */
    public function profile(): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        return response()->json($this->presentProfile());
    }

    /** PATCH /api/pengaturan/profil: only the fields sent change; null or "" clears one. */
    public function updateProfile(Request $request): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        $phone = ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 ()\-.]{5,}$/'];
        $data = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'nama_lengkap' => ['sometimes', 'required', 'string', 'max:255'],
            'slogan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'npsn' => ['sometimes', 'nullable', 'regex:/^[0-9]{8}$/'],
            'nsm' => ['sometimes', 'nullable', 'regex:/^[0-9]{12}$/'],
            'kepala_madrasah' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nip_kepala_madrasah' => ['sometimes', 'nullable', 'regex:/^[0-9 ]{18,21}$/'],
            'alamat' => ['sometimes', 'nullable', 'string', 'max:500'],
            'telepon' => ['sometimes', ...$phone],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'situs' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
            'whatsapp_ptsp' => ['sometimes', ...$phone],
            'whatsapp_pengaduan' => ['sometimes', ...$phone],
            'jam_senin_kamis' => ['sometimes', 'nullable', 'string', 'max:100'],
            'jam_jumat' => ['sometimes', 'nullable', 'string', 'max:100'],
            'jam_sabtu_minggu' => ['sometimes', 'nullable', 'string', 'max:100'],
        ], [
            'nama.required' => 'Nama singkat wajib diisi.',
            'nama_lengkap.required' => 'Nama lengkap instansi wajib diisi.',
            'npsn.regex' => 'NPSN terdiri dari 8 angka.',
            'nsm.regex' => 'NSM terdiri dari 12 angka.',
            'nip_kepala_madrasah.regex' => 'NIP terdiri dari 18 angka.',
            'telepon.regex' => 'Nomor telepon hanya berisi angka (boleh diawali +).',
            'whatsapp_ptsp.regex' => 'Nomor WhatsApp hanya berisi angka (boleh diawali +).',
            'whatsapp_pengaduan.regex' => 'Nomor WhatsApp hanya berisi angka (boleh diawali +).',
        ]);

        $changed = SettingsStore::save(collect($data)->mapWithKeys(fn ($value, string $field) => [self::PROFILE[$field] => $value])->all());

        return response()->json([
            'message' => $changed ? 'Pengaturan disimpan: ' . implode(', ', $changed) . '.' : 'Tidak ada perubahan.',
            'diubah' => $changed,
            'data' => $this->presentProfile(),
        ]);
    }

    /** POST /api/pengaturan/profil/logo (multipart "logo"): replaces the current logo. */
    public function uploadLogo(Request $request): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        $file = $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000'],
        ], [
            'logo.required' => 'Pilih berkas logo.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus PNG, JPG, atau WebP.',
            'logo.max' => 'Logo maksimal 2 MB.',
            'logo.dimensions' => 'Ukuran logo minimal 64×64 piksel dan maksimal 4000×4000 piksel.',
        ])['logo'];

        $old = AppSetting::get('app_logo');
        $path = $file->storeAs(SettingsStore::LOGO_DIRECTORY, 'logo-' . Str::random(12) . '.' . $file->guessExtension(), 'public');
        SettingsStore::save(['app_logo' => 'storage/' . $path]);
        SettingsStore::deleteUploadedLogo($old);

        return response()->json(['message' => 'Logo diperbarui.', 'logo' => asset('storage/' . $path)], 201);
    }

    /** DELETE /api/pengaturan/profil/logo: back to the bundled logo. */
    public function deleteLogo(): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        $old = AppSetting::get('app_logo');
        abort_unless($old, 404, 'Belum ada logo yang diunggah.');

        SettingsStore::save(['app_logo' => null]);
        SettingsStore::deleteUploadedLogo($old);

        return response()->json(null, 204);
    }

    /** GET /api/pengaturan/umum */
    public function general(): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        return response()->json($this->presentGeneral());
    }

    /** PATCH /api/pengaturan/umum: only the fields sent change; null or "" clears one. */
    public function updateGeneral(Request $request): JsonResponse
    {
        $this->authorize('kelola-pengaturan');

        $url = ['sometimes', 'nullable', 'url:http,https', 'max:255'];
        $data = $request->validate([
            'awalan_tiket' => ['sometimes', 'nullable', 'regex:/^[A-Za-z]{2,8}$/'],
            'kode_satker_surat' => ['sometimes', 'nullable', 'regex:/^[A-Za-z0-9.\-]{2,20}$/'],
            'format_tanggal' => ['sometimes', 'nullable', Rule::in(array_keys(Formats::DATE_FORMATS))],
            'kop_baris_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kop_baris_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'deskripsi_footer' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'hak_cipta' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mode_perawatan' => ['sometimes', 'boolean'],
            'pesan_perawatan' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'facebook' => $url, 'instagram' => $url, 'youtube' => $url, 'twitter' => $url,
            'tautan_kemenag' => $url, 'tautan_kanwil' => $url, 'tautan_kankemenag' => $url,
        ], [
            'awalan_tiket.regex' => 'Awalan 2–8 huruf, tanpa angka atau spasi.',
            'kode_satker_surat.regex' => 'Kode satker 2–20 karakter: huruf, angka, titik, atau tanda hubung.',
            'format_tanggal.in' => 'Format tanggal: ' . implode(' | ', array_keys(Formats::DATE_FORMATS)) . '.',
        ]);

        if (array_key_exists('awalan_tiket', $data) && filled($data['awalan_tiket'])) {
            $data['awalan_tiket'] = strtoupper($data['awalan_tiket']);
        }

        $changed = SettingsStore::save(collect($data)->mapWithKeys(fn ($value, string $field) => [self::GENERAL[$field] => $value])->all());

        return response()->json([
            'message' => $changed ? 'Pengaturan disimpan: ' . implode(', ', $changed) . '.' : 'Tidak ada perubahan.',
            'diubah' => $changed,
            'data' => $this->presentGeneral(),
        ]);
    }

    private function presentGeneral(): array
    {
        $values = SettingsStore::values(array_values(self::GENERAL));
        $data = collect(self::GENERAL)->map(fn (string $key) => $values[$key])->all();
        $data['mode_perawatan'] = $data['mode_perawatan'] === 'true';

        return $data + [
            // What new numbers and printed dates look like with these settings
            'contoh' => [
                'nomor_tiket' => Formats::ticketNumber(7, now()),
                'nomor_surat_keluar' => collect(['B-12', $values['surat_kode_satker'] ?: 'MTsN2KM', 'PP.00', now()->format('m'), now()->format('Y')])->join('/'),
                'tanggal' => Formats::date(now()),
            ],
            'pilihan_format_tanggal' => collect(Formats::DATE_FORMATS)->map(fn ($example, $format) => ['nilai' => $format, 'contoh' => now()->translatedFormat($format)])->values(),
        ];
    }

    private function presentProfile(): array
    {
        $values = SettingsStore::values(array_values(self::PROFILE));
        $logo = AppSetting::get('app_logo');

        return collect(self::PROFILE)->map(fn (string $key) => $values[$key])->all() + [
            'logo' => $logo ? asset($logo) : null,
            'logo_diunggah' => (bool) ($logo && str_starts_with($logo, 'storage/' . SettingsStore::LOGO_DIRECTORY . '/')),
        ];
    }
}
