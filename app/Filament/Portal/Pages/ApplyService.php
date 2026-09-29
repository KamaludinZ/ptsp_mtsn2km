<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Service;
use App\Services\TicketService;
use App\Support\ServiceSummary;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * An online service request (Modul 5): the applicant picks the service in
 * the public catalogue and lands here with ?layanan=<slug>.
 */
class ApplyService extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'ajukan';

    protected static ?string $title = 'Ajukan Layanan';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.form-page';

    #[Url(as: 'layanan')]
    public ?string $serviceSlug = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public ?Service $service = null;

    public function mount(): void
    {
        $this->service = Service::query()
            ->where('is_active', true)
            ->where('slug', $this->serviceSlug)
            ->availableFor(auth()->user()->user_type)
            ->first();

        if (! $this->service) {
            Notification::make()
                ->title('Layanan tidak tersedia')
                ->body('Pilih layanan yang tersedia untuk kategori akun Anda di katalog layanan.')
                ->warning()
                ->send();

            $this->redirect(route('onlineportal.service.catalog'));

            return;
        }

        $this->form->fill(['priority' => 'normal']);
    }

    public function getTitle(): string
    {
        return $this->service ? 'Ajukan: ' . $this->service->name : 'Ajukan Layanan';
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Standar pelayanan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('summary')->hiddenLabel()->content(fn () => ServiceSummary::html($this->service)),
                    ]),
                Section::make('Permohonan')
                    ->schema([
                        Textarea::make('description')
                            ->label('Keperluan / keterangan')
                            ->helperText('Jelaskan keperluan Anda dan data yang dibutuhkan petugas.')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4),
                        Select::make('priority')
                            ->label('Tingkat kepentingan')
                            ->options(array_intersect_key(TicketLabels::PRIORITIES, array_flip(['normal', 'high', 'urgent'])))
                            ->required(),
                        FileUpload::make('files')
                            ->label('Berkas persyaratan')
                            ->multiple()
                            ->disk('local')
                            ->directory('ticket-files')
                            ->visibility('private')
                            ->storeFileNamesIn('file_names')
                            ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)
                            ->maxSize(10240)
                            ->maxFiles(10)
                            ->helperText('PDF, JPG, PNG, DOC/DOCX. Maksimal 10 MB per berkas.'),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')->label('Kirim permohonan')->submit('submit')->icon('heroicon-m-paper-airplane'),
            Action::make('cancel')->label('Batal')->color('gray')->url(route('onlineportal.service.catalog')),
        ];
    }

    public function submit(): void
    {
        abort_unless($this->service, 404);

        $data = $this->form->getState();
        $names = $data['file_names'] ?? [];
        $files = collect($data['files'] ?? [])->mapWithKeys(fn ($path) => [$path => $names[$path] ?? basename($path)])->all();
        $user = auth()->user();

        $ticket = app(TicketService::class)->open($this->service, $user, $user, 'online', $data['description'], $data['priority'], $files);

        Notification::make()
            ->success()
            ->title('Permohonan terkirim')
            ->body("Nomor tiket Anda {$ticket->ticket_number}. Kami akan memberi kabar melalui email/WhatsApp.")
            ->actions([
                NotificationAction::make('receipt')->label('Cetak tanda terima')->url(route('tickets.receipt', $ticket))->openUrlInNewTab(),
            ])
            ->persistent()
            ->send();

        $this->redirect(TicketResource::getUrl('view', ['record' => $ticket]));
    }
}
