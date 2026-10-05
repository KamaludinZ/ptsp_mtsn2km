<?php

namespace App\Filament\Portal\Resources\TicketResource\Pages;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Services\TicketService;
use App\Support\TicketDocuments;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
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
            'logs' => fn ($q) => $q->whereIn('action', TicketLog::APPLICANT_VISIBLE)->latest(),
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
            Action::make('upload')
                ->label('Tambah berkas')
                ->icon('heroicon-m-paper-clip')
                ->color('gray')
                ->visible(in_array($ticket->status, Ticket::OPEN_STATUSES, true))
                ->modalDescription('Unggah berkas persyaratan yang belum dilampirkan atau diminta petugas.')
                ->form([
                    FileUpload::make('files')->label('Berkas')->multiple()->maxFiles(10)
                        ->disk('local')->directory('ticket-files')->visibility('private')
                        ->storeFileNamesIn('file_names')
                        ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data) use ($ticket) {
                    foreach ($data['files'] as $path) {
                        app(TicketService::class)->attachFile($ticket, $path, $data['file_names'][$path] ?? basename($path), auth()->user(), 'Berkas susulan dari pemohon.');
                    }
                    $this->record = $this->resolveRecord($ticket->getKey());
                    Notification::make()->title('Berkas ditambahkan')->success()->send();
                }),
            Action::make('receipt')
                ->label('Tanda terima')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('tickets.receipt', $ticket))
                ->openUrlInNewTab(),
        ];
    }
}
