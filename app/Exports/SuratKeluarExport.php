<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Buku register surat keluar, exactly as filtered on the staff panel. */
class SuratKeluarExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query->with('pembuat:id,name');
    }

    public function title(): string
    {
        return 'Register Surat Keluar';
    }

    public function headings(): array
    {
        return ['No. Urut', 'Nomor Surat', 'Tanggal', 'Tujuan', 'Perihal', 'Jenis', 'Klasifikasi', 'Lampiran', 'Tembusan', 'Keterangan', 'Pembuat'];
    }

    /** @param  SuratKeluar  $letter */
    public function map($letter): array
    {
        return [
            $letter->nomor_urut,
            $letter->nomor_surat,
            $letter->tanggal_surat?->format('Y-m-d'),
            $letter->tujuan_surat,
            $letter->perihal,
            $letter->jenis_surat,
            $letter->klasifikasi,
            $letter->lampiran,
            str_replace("\n", '; ', (string) $letter->tembusan),
            $letter->keterangan,
            $letter->pembuat?->name,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
