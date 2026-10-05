<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Concerns\NotifiesActionResult;
use App\Filament\Forms\DispositionForm;
use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\IncomingCategory;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
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
                $this->categoryAction(),
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
                    ->options(fn () => User::assignable()->orderBy('name')->pluck('name', 'id'))
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
            ->modalHeading('Ubah status permohonan')
            ->modalDescription(fn () => 'Status saat ini: ' . TicketLabels::status($this->getRecord()->status) . '. Perubahan tercatat di riwayat status dan pemohon diberi tahu.')
            ->modalSubmitActionLabel('Simpan status')
            ->disabled(fn () => ! $this->service()->nextStatuses($this->getRecord(), auth()->user()))
            ->tooltip(fn () => $this->service()->nextStatuses($this->getRecord(), auth()->user()) ? null : 'Permohonan sudah ditutup.')
            ->form([
                ToggleButtons::make('status')
                    ->label('Status baru')
                    ->options(fn () => $this->service()->nextStatuses($this->getRecord(), auth()->user()))
                    ->colors(fn () => collect($this->service()->nextStatuses($this->getRecord(), auth()->user()))->mapWithKeys(fn ($label, $status) => [$status => TicketLabels::statusColor($status)])->all())
                    ->icons([
                        'submitted' => 'heroicon-m-arrow-uturn-left',
                        'verified' => 'heroicon-m-check',
                        'in_process' => 'heroicon-m-cog-6-tooth',
                        'completed' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                        'cancelled' => 'heroicon-m-no-symbol',
                    ])
                    ->inline()
                    ->required()
                    ->live()
                    ->helperText(fn (Get $get) => TicketService::STATUS_HINTS[$get('status')] ?? ($this->getRecord()->needsApproval() ? 'Menunggu disposisi pimpinan: status Selesai belum tersedia.' : 'Pilih status berikutnya.')),
                Textarea::make('notes')
                    ->label(fn (Get $get) => $get('status') === 'rejected' ? 'Alasan penolakan' : 'Catatan')
                    ->placeholder(fn (Get $get) => $get('status') === 'rejected' ? 'Jelaskan kepada pemohon mengapa permohonan ditolak.' : 'Contoh: Berkas sudah lengkap, diteruskan ke TU.')
                    ->required()
                    ->minLength(fn (Get $get) => $get('status') === 'rejected' ? 10 : 3)
                    ->maxLength(1000)
                    ->rows(3),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->changeStatus($this->getRecord(), $data['status'], $data['notes'], auth()->user()), 'Status menjadi ' . TicketLabels::status($data['status']) . '.');
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
            ->label('Catatan tindak lanjut')
            ->icon('heroicon-m-pencil-square')
            ->modalHeading('Catatan tindak lanjut')
            ->modalDescription('Catat apa yang sudah dilakukan untuk permohonan ini. Catatan tersimpan di riwayat layanan dan tidak dapat diubah.')
            ->modalSubmitActionLabel('Simpan catatan')
            ->form([
                Select::make('type')
                    ->label('Jenis tindak lanjut')
                    ->options(TicketService::FOLLOW_UP_TYPES)
                    ->default('internal')
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('to_applicant', in_array($state, ['contact_applicant', 'request_documents'], true))),
                Textarea::make('note')
                    ->label('Catatan')
                    ->required()
                    ->minLength(5)
                    ->maxLength(1000)
                    ->rows(4)
                    ->placeholder('Contoh: Pemohon dihubungi lewat WhatsApp, diminta melengkapi fotokopi KK.'),
                Toggle::make('to_applicant')
                    ->label('Tampilkan ke pemohon')
                    ->helperText(fn (Get $get) => $get('to_applicant')
                        ? 'Pemohon dapat membaca catatan ini di portal dan halaman lacak tiket. Jangan tulis informasi internal.'
                        : 'Hanya petugas yang dapat membaca catatan ini.')
                    ->live(),
                DatePicker::make('next_follow_up_at')
                    ->label('Tindak lanjut berikutnya')
                    ->helperText('Opsional: tanggal rencana tindak lanjut berikutnya.')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->minDate(today()),
            ])
            ->action(function (array $data) {
                self::attempt(fn () => $this->service()->addNote(
                    $this->getRecord(),
                    $data['note'],
                    auth()->user(),
                    $data['type'] ?? null,
                    (bool) ($data['to_applicant'] ?? false),
                    $data['next_follow_up_at'] ?? null,
                ), ($data['to_applicant'] ?? false) ? 'Catatan disimpan dan ditampilkan ke pemohon.' : 'Catatan internal disimpan.');
                $this->refreshTicket();
            });
    }

    /** Pilih kategori layanan masuk (disposisi, tembusan, koordinasi, arahan). */
    private function categoryAction(): Actions\Action
    {
        return Actions\Action::make('category')
            ->label('Kategori layanan masuk')
            ->icon('heroicon-m-tag')
            ->modalHeading('Pilih kategori layanan masuk')
            ->modalDescription(fn () => 'Menurut instruksi pimpinan: ' . IncomingCategory::label(IncomingCategory::derived($this->getRecord()))
                . '. Pilih kategori lain bila permohonan perlu dikelompokkan berbeda di menu Layanan Masuk.')
            ->modalSubmitActionLabel('Simpan kategori')
            ->visible(fn () => TicketService::canChooseCategory($this->getRecord()))
            ->fillForm(fn () => ['category' => $this->getRecord()->incoming_category ?? 'auto'])
            ->form([
                Radio::make('category')
                    ->label('Kategori')
                    ->options(['auto' => 'Ikuti instruksi pimpinan (' . IncomingCategory::label(IncomingCategory::derived($this->getRecord())) . ')'] + IncomingCategory::CATEGORIES)
                    ->descriptions(['auto' => 'Kategori ditentukan otomatis dari instruksi disposisi.'] + IncomingCategory::DESCRIPTIONS)
                    ->required(),
                Textarea::make('reason')
                    ->label('Alasan')
                    ->placeholder('Contoh: cukup sebagai tembusan untuk arsip TU.')
                    ->required()
                    ->minLength(5)
                    ->maxLength(500)
                    ->rows(2),
            ])
            ->action(function (array $data) {
                $category = $data['category'] === 'auto' ? null : $data['category'];
                self::attempt(fn () => $this->service()->setIncomingCategory($this->getRecord(), $category, auth()->user(), $data['reason']),
                    'Kategori menjadi ' . IncomingCategory::label($category ?? IncomingCategory::derived($this->getRecord())) . '.');
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
            ->label('Disposisikan')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->visible(fn () => $this->awaitingMyDecision())
            ->modalHeading('Disposisi permohonan')
            ->form(fn () => DispositionForm::schema($this->getRecord()))
            ->action(function (array $data) {
                $done = self::attempt(fn () => DispositionForm::submit($this->getRecord(), $data), 'Permohonan telah didisposisi. Petugas dapat menyiapkan produk layanan.');
                $this->refreshTicket();

                // Leaders usually work through the queue: take them back to the next request.
                if ($done && Ticket::approvableBy(auth()->user())->isNotEmpty()) {
                    $this->redirect(Approvals::getUrl());
                }
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
