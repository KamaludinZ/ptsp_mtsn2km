<?php

namespace App\Filament\Portal\Resources\TicketResource\Pages;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function getTitle(): string
    {
        return 'Permohonan ' . $this->getRecord()->ticket_number;
    }

    protected function resolveRecord(int | string $key): Ticket
    {
        // Applicants never see internal notes; only milestones are shown.
        return parent::resolveRecord($key)->load([
            'files',
            'output',
            'logs' => fn ($q) => $q->whereIn('action', ['created', 'status_changed', 'assigned', 'approved', 'rejected', 'output_uploaded', 'picked_up', 'workflow_completed'])->latest(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        $ticket = $this->getRecord();

        return [
            Action::make('download')
                ->label('Unduh hasil layanan')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->visible($ticket->status === 'completed' && (bool) $ticket->output)
                ->url(fn () => route('documents.ticket-output', $ticket->output))
                ->openUrlInNewTab(),
            Action::make('survey')
                ->label('Beri penilaian layanan')
                ->icon('heroicon-m-star')
                ->visible($ticket->status === 'completed' && ! $ticket->surveyResponses()->exists())
                ->url(fn () => route('survey.form', ['tiket' => $ticket->ticket_number])),
            Action::make('receipt')
                ->label('Tanda terima')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('tickets.receipt', $ticket))
                ->openUrlInNewTab(),
        ];
    }
}
