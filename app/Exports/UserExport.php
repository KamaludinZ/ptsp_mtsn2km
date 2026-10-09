<?php

namespace App\Exports;

use App\Models\User;
use App\Services\FrontDeskService;
use App\Support\RoleAccess;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Daftar akun pengguna, persis seperti tab, pencarian, dan filter di panel. Tanpa kata sandi. */
class UserExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private Builder $query, private string $tab = 'Semua')
    {
    }

    public function query(): Builder
    {
        return $this->query->with('roles:id,name');
    }

    public static function filename(string $tab): string
    {
        return 'akun-pengguna-' . \Illuminate\Support\Str::slug($tab) . '-' . now()->format('Ymd-His') . '.xlsx';
    }

    public function title(): string
    {
        return 'Akun ' . $this->tab;
    }

    public function headings(): array
    {
        return ['Nama', 'Email', 'Nomor WhatsApp', 'Tipe Pengguna', 'Kode Registrasi', 'Role', 'Status', 'Terakhir Masuk', 'Dibuat'];
    }

    /** @param  User  $user */
    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->whatsapp_number,
            FrontDeskService::APPLICANT_TYPES[$user->user_type] ?? $user->user_type,
            $user->registration_code,
            $user->roles->pluck('name')->map(fn (string $role) => RoleAccess::roleLabel($role))->join(', '),
            $user->is_active ? 'Aktif' : 'Nonaktif',
            $user->last_login_at?->format('Y-m-d H:i'),
            $user->created_at?->format('Y-m-d'),
        ];
    }

    public function columnFormats(): array
    {
        // Nomor WhatsApp tetap teks agar nol di depan tidak hilang.
        return ['C' => NumberFormat::FORMAT_TEXT];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
