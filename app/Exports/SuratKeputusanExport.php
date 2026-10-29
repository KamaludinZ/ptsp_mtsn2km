<?php

namespace App\Exports;

use App\Models\SuratKeputusan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Register Penomoran SK, exactly as filtered on the staff panel or asked for through the API (SkReportService). */
class SuratKeputusanExport extends TextValueBinder implements FromQuery, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return 'Register Penomoran SK';
    }

    public function headings(): array
    {
        return ['No. Urut', 'Nomor SK', 'Tanggal', 'Perihal', 'Jenis SK', 'Nama Peminta', 'Keterangan', 'Status', 'Scan', 'Petugas'];
    }

    /** @param  SuratKeputusan  $sk */
    public function map($sk): array
    {
        return [
            $sk->nomor_urut,
            $sk->nomor_sk,
            $sk->tanggal_surat?->format('Y-m-d'),
            $sk->perihal,
            $sk->jenis_sk,
            $sk->nama_peminta,
            $sk->keterangan,
            SuratKeputusan::STATUS[$sk->status()],
            $sk->scanName(),
            $sk->pembuat?->name,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Headings stay in view and every column can be filtered in the spreadsheet.
        $sheet->freezePane('C2');
        $sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . '1');

        return [1 => ['font' => ['bold' => true]]];
    }
}
