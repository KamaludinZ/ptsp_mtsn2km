<?php

namespace App\Exports;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Imports\UserImport;
use App\Services\FrontDeskService;
use App\Support\RoleAccess;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import akun: sheet "Akun" untuk diisi (dibaca UserImport) dan
 * sheet "Petunjuk" berisi kode tipe pengguna dan daftar role sistem.
 */
class UserImportTemplateExport implements WithMultipleSheets
{
    public const FILENAME = 'template-import-akun.xlsx';

    public function sheets(): array
    {
        $guide = [
            ['Petunjuk import akun'],
            ['Isi sheet "Akun" mulai baris 2, satu baris satu akun. Jangan ubah judul kolom di baris 1.'],
            ['Wajib: nama, email, tipe_pengguna. Opsional: nomor_whatsapp, kode_registrasi, role.'],
            ['Kata sandi dibuat otomatis, tidak dikirim, dan pengguna diminta menggantinya. Tanpa email undangan.'],
            ['Role boleh lebih dari satu, pisahkan dengan koma. Bila kosong, akun memakai role sesuai tipe_pengguna.'],
            [''],
            ['Kode tipe_pengguna', 'Keterangan'],
            ...self::pairs(FrontDeskService::APPLICANT_TYPES),
            [''],
            ['Kode role (' . count(RoleAccess::SYSTEM_ROLES) . ' peran sistem)', 'Keterangan'],
            ...self::pairs(RoleAccess::SYSTEM_ROLES),
        ];

        return [
            new class implements FromArray, ShouldAutoSize, WithStyles, WithTitle
            {
                public function array(): array
                {
                    return [ListUsers::IMPORT_COLUMNS, ['Contoh Guru', 'guru@contoh.sch.id', '', 'guru', 'GURU-001', 'guru']];
                }

                public function title(): string
                {
                    return 'Akun';
                }

                public function styles(Worksheet $sheet): array
                {
                    // Nomor WhatsApp sebagai teks agar nol di depan tidak hilang.
                    // Hanya sampai batas baris import: format satu kolom penuh membuat berkas berat dibaca.
                    $sheet->getStyle('C2:C' . (UserImport::MAX_ROWS + 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    $sheet->setCellValueExplicit('C2', '081234567890', DataType::TYPE_STRING);
                    $sheet->freezePane('A2');

                    return [1 => ['font' => ['bold' => true]]];
                }
            },
            new class($guide) implements FromArray, ShouldAutoSize, WithStyles, WithTitle
            {
                public function __construct(private array $rows)
                {
                }

                public function array(): array
                {
                    return $this->rows;
                }

                public function title(): string
                {
                    return 'Petunjuk';
                }

                public function styles(Worksheet $sheet): array
                {
                    $headers = array_keys(array_filter($this->rows, fn (array $row) => str_starts_with($row[0] ?? '', 'Kode ')));

                    return collect([0, ...$headers])->mapWithKeys(fn (int $i) => [$i + 1 => ['font' => ['bold' => true]]])->all();
                }
            },
        ];
    }

    /** @return array<int, array{0: string, 1: string}> */
    private static function pairs(array $items): array
    {
        return collect($items)->map(fn (string $label, string $code) => [$code, $label])->values()->all();
    }
}
