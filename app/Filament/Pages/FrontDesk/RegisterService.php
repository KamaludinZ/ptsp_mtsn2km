<?php

namespace App\Filament\Pages\FrontDesk;

use App\Filament\Resources\TicketResource;
use App\Models\Service;
use App\Services\FrontDeskService;
use App\Support\ServiceSummary;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * A walk-in applicant's request registered at the counter on their behalf
 * (mode offline). The applicant's account is found by e-mail or WhatsApp
 * number, or created.
 */
class RegisterService extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationGroup = 'Loket';

    protected static ?string $navigationLabel = 'Registrasi Layanan';

    protected static ?string $title = 'Registrasi Layanan Loket';

    protected static ?string $slug = 'loket/registrasi-layanan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.form-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('frontdesk.access');
    }

    public function getSubheading(): ?string
    {
        return 'Daftarkan permohonan pemohon yang datang langsung ke loket PTSP.';
    }

    public function mount(): void
    {
        $this->form->fill(['priority' => 'normal', 'applicant_type' => 'umum']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Pemohon')
                    ->description('Jika email atau nomor WhatsApp sudah terdaftar, permohonan masuk ke akun tersebut.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('applicant_name')->label('Nama lengkap')->required()->maxLength(255),
                        Select::make('applicant_type')->label('Kategori pemohon')->options(FrontDeskService::APPLICANT_TYPES)->required()->live(),
                        TextInput::make('applicant_phone')->label('Nomor WhatsApp')->tel()->required()->maxLength(20),
                        TextInput::make('applicant_email')->label('Email (opsional)')->email()->maxLength(255),
                    ]),
                Section::make('Permohonan')
                    ->columns(2)
                    ->schema([
                        Select::make('service_id')
                            ->label('Layanan')
                            ->options(fn (Get $get) => Service::where('is_active', true)
                                ->availableFor($get('applicant_type') ?: 'umum')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        Placeholder::make('service_summary')
                            ->label('Standar pelayanan')
                            ->visible(fn (Get $get) => filled($get('service_id')))
                            ->content(fn (Get $get) => ServiceSummary::html(Service::find($get('service_id'))))
                            ->columnSpanFull(),
                        Textarea::make('description')->label('Keperluan / keterangan')->required()->maxLength(1000)->rows(4)->columnSpanFull(),
                        Select::make('priority')->label('Prioritas')->options(array_intersect_key(TicketLabels::PRIORITIES, array_flip(['normal', 'high', 'urgent'])))->required(),
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
                            ->helperText('PDF, JPG, PNG, DOC/DOCX. Maksimal 10 MB per berkas.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')->label('Daftarkan permohonan')->submit('submit')->icon('heroicon-m-check'),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $names = $data['file_names'] ?? [];
        $files = collect($data['files'] ?? [])->mapWithKeys(fn ($path) => [$path => $names[$path] ?? basename($path)])->all();

        $ticket = app(FrontDeskService::class)->registerWalkIn($data, $files, auth()->user());

        Notification::make()
            ->success()
            ->title('Permohonan terdaftar dengan nomor ' . $ticket->ticket_number)
            ->body('Sampaikan nomor tiket kepada pemohon untuk melacak permohonannya.')
            ->actions([
                NotificationAction::make('receipt')->label('Cetak tanda terima')->url(route('tickets.receipt', $ticket))->openUrlInNewTab(),
            ])
            ->persistent()
            ->send();

        $this->redirect(TicketResource::getUrl('view', ['record' => $ticket]));
    }
}
