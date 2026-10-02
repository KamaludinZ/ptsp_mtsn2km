<?php

namespace App\Exports;

use App\Models\TicketLog;
use App\Support\TicketLabels;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Riwayat layanan for audit, exactly as filtered on the staff panel. */
class ServiceHistoryExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query->with(['ticket:id,ticket_number,service_id', 'ticket.service:id,name', 'performer:id,name']);
    }

    public function title(): string
    {
        return 'Riwayat Layanan';
    }

    public function headings(): array
    {
        return ['Waktu', 'No. Tiket', 'Layanan', 'Kegiatan', 'Status Awal', 'Status Akhir', 'Pelaku', 'Catatan'];
    }

    /** @param  TicketLog  $log */
    public function map($log): array
    {
        return [
            $log->created_at?->format('Y-m-d H:i:s'),
            $log->ticket?->ticket_number,
            $log->ticket?->service?->name,
            TicketLabels::logAction($log->action),
            $log->from_status ? TicketLabels::status($log->from_status) : null,
            $log->to_status ? TicketLabels::status($log->to_status) : null,
            $log->performer?->name ?? 'Sistem',
            $log->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
