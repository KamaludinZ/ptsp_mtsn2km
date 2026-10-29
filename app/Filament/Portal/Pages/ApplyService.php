<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Service;
use App\Services\TicketService;
use App\Support\PortalServices;
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
 * An online service request (Modul 5), the portal's "Ajukan Layanan" menu.
 * Opened from a service (?layanan=<slug>, e.g. from the catalogue) it is
 * that service's form; opened from the menu it first asks which service,
 * among those the applicant's roles may request online (PortalServices).
 */
class ApplyService extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'ajukan';

    protected static ?string $title = 'Ajukan Layanan';

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Ajukan Layanan';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.pages.form-page';

    #[Url(as: 'layanan')]
    public ?string $serviceSlug = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public ?Service $service = null;

    public function mount(): void
    {
        if (blank($this->serviceSlug)) {
            $this->form->fill(); // from the menu: pick the service first

            return;
        }

        $this->service = PortalServices::requestable(auth()->user())->where('slug', $this->serviceSlug)->first();

        if (! $this->service) {
            Notification::make()
                ->title('Layanan tidak tersedia')
                ->body('Pilih layanan yang tersedia untuk kategori akun Anda di katalog layanan.')
                ->warning()
                ->send();

            $this->redirect(ServiceCatalog::getUrl());

            return;
        }

        $this->form->fill(['priority' => 'normal']);
    }

    public function getTitle(): string
    {
        return $this->service ? 'Ajukan: ' . $this->service->name : 'Ajukan Layanan';
    }

    public function getSubheading(): ?string
    {
        return $this->service ? null : 'Pilih layanan yang ingin Anda ajukan; formulirnya terbuka setelah itu.';
    }

    public function form(Form $form): Form
    {
        // Built before mount() knows the service: each part shows by it (picker, or the service's form).
        $chosen = fn () => (bool) $this->service;

        return $form
            ->statePath('data')
            ->schema([
                Section::make('Layanan')
                    ->visible(fn () => ! $this->service)
                    ->schema([
                        Select::make('service')
                            ->label('Layanan yang diajukan')
                            ->options(fn () => PortalServices::requestable(auth()->user())->orderBy('name')->pluck('name', 'slug'))
                            ->searchable()
                            ->required()
                            ->validationMessages(['required' => 'Pilih layanan yang ingin Anda ajukan.'])
                            ->helperText(new \Illuminate\Support\HtmlString('Belum yakin? Bandingkan layanan di <a href="' . e(ServiceCatalog::getUrl()) . '" class="underline">Katalog Layanan</a>.')),
                    ]),
                Section::make('Standar pelayanan')
                    ->visible($chosen)
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('summary')->hiddenLabel()->content(fn () => ServiceSummary::html($this->service)),
                    ]),
                Section::make('Template berkas')
                    ->icon('heroicon-o-document-arrow-down')
                    ->description('Unduh, lengkapi, lalu unggah bersama berkas persyaratan di bawah.')
                    ->visible(fn () => (bool) $this->service?->activeTemplates->isNotEmpty())
                    ->schema([
                        Placeholder::make('templates')->hiddenLabel()->content(fn () => ServiceSummary::templates($this->service)),
                    ]),
                Section::make('Permohonan')
                    ->visible($chosen)
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
        if (! $this->service) {
            return [
                Action::make('submit')->label('Lanjut ke formulir')->submit('submit')->icon('heroicon-m-arrow-right'),
                Action::make('cancel')->label('Batal')->color('gray')->url(ServiceCatalog::getUrl()),
            ];
        }

        return [
            Action::make('submit')->label('Kirim permohonan')->submit('submit')->icon('heroicon-m-paper-airplane'),
            Action::make('change')->label('Ganti layanan')->color('gray')->url(self::getUrl()),
            Action::make('cancel')->label('Batal')->color('gray')->url(ServiceCatalog::getUrl()),
        ];
    }

    public function submit(): void
    {
        if (! $this->service) {
            // The service picked: on to its form.
            $this->redirect(self::getUrl(['layanan' => $this->form->getState()['service']]), navigate: true);

            return;
        }

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
