<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Concerns\NotifiesActionResult;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

/**
 * One ticket and everything that can be done with it. Each action is shown
 * only to the roles allowed to take it, and the rules themselves live in
 * TicketService.
 */
class ViewTicket extends ViewRecord
{
    use NotifiesActionResult;

    protected static string $resource = TicketResource::class;

    public function getTitle(): string
    {
        return 'Tiket ' . $this->getRecord()->ticket_number;
    }

    public function getSubheading(): ?string
    {
        $ticket = $this->getRecord();

        return trim(($ticket->service?->name ?? '') . ' · ' . ($ticket->user?->name ?? ''), ' ·');
    }

    protected function resolveRecord(int | string $key): Ticket
    {
        return $this->loadTicket(parent::resolveRecord($key));
    }

    private function loadTicket(Ticket $ticket): Ticket
    {
        return $ticket->load([
            'user', 'service.workflow', 'assignedTo', 'approver', 'files', 'output',
            'logs' => fn ($q) => $q->with('performer:id,name')->latest(),
            'workflowSteps' => fn ($q) => $q->with('workflowStep')->orderBy('ticket_workflow_steps.id'),
        ]);
    }

    private function refreshTicket(): void
    {
        $this->record = $this->loadTicket($this->getRecord()->fresh());
    }

    private function service(): TicketService
    {
        return app(TicketService::class);
    }

    private function canWork(): bool
    {
        return (bool) auth()->user()?->can('update', $this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->approveAction(),
            $this->rejectAction(),
            $this->handOverAction(),
            $this->uploadOutputAction(),
            Actions\ActionGroup::make([
                $this->assignAction(),
                $this->statusAction(),
                $this->priorityAction(),
                $this->completeStepAction(),
                $this->noteAction(),
                $this->uploadFileAction(),
            ])
                ->label('Proses tiket')
                ->icon('heroicon-m-cog-6-tooth')
                ->button()
                ->color('gray')
                ->visible(fn () => $this->canWork()),
            Actions\Action::make('receipt')
                ->label('Tanda terima')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('tickets.receipt', $this->getRecord()))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()->successRedirectUrl(TicketResource::getUrl('index')),
        ];
    }

    private function assignAction(): Actions\Action
    {
        return Actions\Action::make('assign')
            ->label('Tugaskan petugas')
            ->icon('heroicon-m-user-plus')
            ->form([
                Select::make('staff_id')
                    ->label('Petugas')
                    ->options(fn () => User::query()
                        ->where('is_active', true)
                        ->where(fn ($q) => $q->whereIn('user_type', ['guru', 'pegawai'])->orWhereHas('roles', fn ($r) => $r->whereIn('name', User::STAFF_ROLES)))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->default(fn () => $this->getRecord()->assigned_to_id)
                    ->searchable()
                    ->required(),
                Textarea::make('notes')->label('Catatan')->maxLength(500)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->assign($this->getRecord(), User::findOrFail($data['staff_id']), auth()->user(), $data['notes'] ?? null), 'Tiket ditugaskan.');
                $this->refreshTicket();
            });
    }

    private function statusAction(): Actions\Action
    {
        return Actions\Action::make('changeStatus')
            ->label('Ubah status')
            ->icon('heroicon-m-arrow-path')
            ->form([
                Select::make('status')
                    ->label('Status baru')
                    ->options(TicketService::OFFICER_STATUSES)
                    ->default(fn () => array_key_exists($this->getRecord()->status, TicketService::OFFICER_STATUSES) ? $this->getRecord()->status : null)
                    ->helperText('Persetujuan diberikan pimpinan melalui menu Persetujuan.')
                    ->required(),
                Textarea::make('notes')->label('Catatan')->required()->maxLength(1000)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->changeStatus($this->getRecord(), $data['status'], $data['notes'], auth()->user()), 'Status tiket diperbarui.');
                $this->refreshTicket();
            });
    }

    private function priorityAction(): Actions\Action
    {
        return Actions\Action::make('priority')
            ->label('Ubah prioritas')
            ->icon('heroicon-m-flag')
            ->form([
                Select::make('priority')->label('Prioritas')->options(TicketLabels::PRIORITIES)
                    ->default(fn () => $this->getRecord()->priority)->required(),
            ])
            ->action(function (array $data) {
                $this->getRecord()->update(['priority' => $data['priority']]);
                self::attempt(fn () => $this->service()->addNote($this->getRecord(), 'Prioritas diubah menjadi ' . TicketLabels::priority($data['priority']) . '.', auth()->user()), 'Prioritas diperbarui.');
                $this->refreshTicket();
            });
    }

    private function noteAction(): Actions\Action
    {
        return Actions\Action::make('note')
            ->label('Tambah catatan')
            ->icon('heroicon-m-pencil-square')
            ->form([
                Textarea::make('note')->label('Catatan')->required()->maxLength(1000)->rows(4),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->addNote($this->getRecord(), $data['note'], auth()->user()), 'Catatan ditambahkan.');
                $this->refreshTicket();
            });
    }

    private function uploadFileAction(): Actions\Action
    {
        return Actions\Action::make('uploadFile')
            ->label('Unggah berkas')
            ->icon('heroicon-m-paper-clip')
            ->form([
                FileUpload::make('file')
                    ->label('Berkas')
                    ->disk('local')
                    ->directory('ticket-files')
                    ->visibility('private')
                    ->storeFileNamesIn('file_name')
                    ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)
                    ->maxSize(10240)
                    ->required(),
                TextInput::make('description')->label('Keterangan')->maxLength(255),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->attachFile($this->getRecord(), $data['file'], $data['file_name'] ?? basename($data['file']), auth()->user(), $data['description'] ?? null), 'Berkas diunggah.');
                $this->refreshTicket();
            });
    }

    private function uploadOutputAction(): Actions\Action
    {
        return Actions\Action::make('uploadOutput')
            ->label(fn () => $this->getRecord()->output ? 'Ganti hasil layanan' : 'Unggah hasil layanan')
            ->icon('heroicon-m-document-arrow-up')
            ->color('success')
            ->visible(fn () => $this->canWork()
                && ! $this->getRecord()->needsApproval()
                && ! in_array($this->getRecord()->status, ['rejected', 'cancelled'], true))
            ->modalDescription('Tiket otomatis ditandai selesai setelah hasil layanan diunggah.')
            ->form([
                FileUpload::make('file')
                    ->label('Berkas hasil layanan')
                    ->disk('local')
                    ->directory('ticket-outputs')
                    ->visibility('private')
                    ->storeFileNamesIn('file_name')
                    ->acceptedFileTypes(TicketDocuments::MIME_TYPES)
                    ->maxSize(20480)
                    ->required(),
                Textarea::make('description')->label('Keterangan')->maxLength(500)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->uploadOutput($this->getRecord(), $data['file'], $data['file_name'] ?? basename($data['file']), auth()->user(), $data['description'] ?? null), 'Hasil layanan diunggah.');
                $this->refreshTicket();
            });
    }

    private function completeStepAction(): Actions\Action
    {
        return Actions\Action::make('completeStep')
            ->label('Selesaikan langkah workflow')
            ->icon('heroicon-m-check-circle')
            ->visible(fn () => $this->getRecord()->workflowSteps->whereNull('completed_at')->isNotEmpty())
            ->form([
                Select::make('step_id')
                    ->label('Langkah')
                    ->options(fn () => $this->getRecord()->workflowSteps
                        ->whereNull('completed_at')
                        ->mapWithKeys(fn ($step) => [$step->workflow_step_id => $step->workflowStep?->name ?? 'Langkah #' . $step->workflow_step_id]))
                    ->required(),
                Textarea::make('notes')->label('Catatan')->maxLength(500)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->completeWorkflowStep($this->getRecord(), (int) $data['step_id'], auth()->user(), $data['notes'] ?? null), 'Langkah workflow diselesaikan.');
                $this->refreshTicket();
            });
    }

    private function awaitingMyDecision(): bool
    {
        $ticket = $this->getRecord();

        return (bool) auth()->user()?->can('approve', $ticket)
            && Ticket::whereKey($ticket->id)->awaitingApproval()->exists();
    }

    private function approveAction(): Actions\Action
    {
        return Actions\Action::make('approve')
            ->label('Setujui')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->visible(fn () => $this->awaitingMyDecision())
            ->form([
                Radio::make('signature_type')
                    ->label('Jenis tanda tangan')
                    ->options(['tte' => 'TTE (tanda tangan elektronik)', 'ttd' => 'TTD (tanda tangan basah)'])
                    ->required(),
                Textarea::make('notes')->label('Catatan (opsional)')->maxLength(500)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->decide($this->getRecord(), true, auth()->user(), $data['signature_type'], $data['notes'] ?? null), 'Permohonan disetujui. Petugas TU dapat menyiapkan produk layanan.');
                $this->refreshTicket();
            });
    }

    private function rejectAction(): Actions\Action
    {
        return Actions\Action::make('reject')
            ->label('Tolak')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->visible(fn () => $this->awaitingMyDecision())
            ->form([
                Textarea::make('notes')->label('Alasan penolakan')->required()->maxLength(500)->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->decide($this->getRecord(), false, auth()->user(), null, $data['notes']), 'Permohonan ditolak.');
                $this->refreshTicket();
            });
    }

    private function handOverAction(): Actions\Action
    {
        return Actions\Action::make('handOver')
            ->label('Serahkan ke pemohon')
            ->icon('heroicon-m-hand-raised')
            ->color('success')
            ->visible(fn () => auth()->user()?->can('handOver', $this->getRecord())
                && $this->getRecord()->status === 'completed'
                && $this->getRecord()->ready_for_pickup)
            ->requiresConfirmation()
            ->modalDescription('Pastikan produk layanan sudah diterima pemohon.')
            ->action(function () {
                self::attempt(fn () => $this->service()->handOver($this->getRecord(), auth()->user()), 'Produk layanan sudah diserahkan.');
                $this->refreshTicket();
            });
    }
}
