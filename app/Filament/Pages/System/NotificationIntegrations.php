<?php

namespace App\Filament\Pages\System;

use App\Models\NotificationSetting;
use App\Services\NotificationGateway;
use App\Support\WhatsAppGateways;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Pengaturan integrasi Email & WhatsApp: gateway configuration and an
 * on/off switch per channel, separately for each line (jalur pelayanan and
 * jalur pengaduan, saran & WBS). Secrets are stored encrypted and never sent
 * back to the browser; leaving them blank keeps the saved value.
 */
class NotificationIntegrations extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Integrasi Notifikasi';

    protected static ?string $title = 'Integrasi Email & WhatsApp';

    protected static ?string $slug = 'integrasi-notifikasi';

    protected static string $view = 'filament.pages.form-page';

    /** What each line carries, shown on its tab. */
    private const LINE_SCOPES = [
        'pelayanan' => 'Notifikasi permohonan layanan: permohonan diterima, perubahan status, disposisi, selesai/ditolak, dan pengingat survei.',
        'pengaduan' => 'Notifikasi pengaduan, saran, dan whistleblowing (WBS) ke pelapor dan petugas pengaduan. Memakai akun email dan nomor WhatsApp tersendiri, tidak lewat jalur pelayanan. Isi pesannya tetap diatur di Template Notifikasi.',
    ];

    private const LINE_ICONS = [
        'pelayanan' => 'heroicon-o-clipboard-document-list',
        'pengaduan' => 'heroicon-o-megaphone',
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public function getSubheading(): ?string
    {
        return 'Gateway pengiriman notifikasi ke pemohon dan petugas, terpisah per jalur. Kanal yang dinonaktifkan tidak mengirim pesan apa pun.';
    }

    public function mount(): void
    {
        $this->form->fill(collect(NotificationSetting::LINES)->map(fn (string $label, string $line) => self::stored($line))->all());
    }

    /** A line's saved settings; the pelayanan line starts from .env until saved. */
    private static function stored(string $line): array
    {
        $email = NotificationSetting::for('email', $line);
        $whatsapp = NotificationSetting::for('whatsapp', $line);
        $env = fn (string $key) => $line === 'pelayanan' ? config($key) : null;

        return [
            'email' => [
                'is_enabled' => $email->is_enabled,
                'host' => $email->value('host', $env('mail.mailers.smtp.host')),
                'port' => $email->value('port', $env('mail.mailers.smtp.port')),
                'encryption' => $email->value('encryption', $env('mail.mailers.smtp.encryption') ?: 'none'),
                'username' => $email->value('username', $env('mail.mailers.smtp.username')),
                'from_address' => $email->value('from_address', $env('mail.from.address')),
                'from_name' => $email->value('from_name', $env('mail.from.name')),
            ],
            'whatsapp' => [
                'is_enabled' => $whatsapp->is_enabled,
                'provider' => $whatsapp->value('provider', 'custom'),
                'api_url' => $whatsapp->value('api_url', $env('whatsapp.api_url')),
                'sender_id' => $whatsapp->value('sender_id', $env('whatsapp.sender_id')),
            ],
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Tabs::make('Jalur')
                    ->tabs(collect(NotificationSetting::LINES)->map(fn (string $label, string $line) => Tabs\Tab::make('Jalur ' . $label)
                        ->icon(self::LINE_ICONS[$line])
                        ->statePath($line)
                        ->schema([
                            Placeholder::make('cakupan')->hiddenLabel()->content(self::LINE_SCOPES[$line]),
                            ...$this->channelSections($line),
                        ]))->values()->all()),
            ]);
    }

    /** The Email and WhatsApp gateway of one line. */
    private function channelSections(string $line): array
    {
        $email = NotificationSetting::for('email', $line);
        $whatsapp = NotificationSetting::for('whatsapp', $line);
        $secretHint = fn (NotificationSetting $setting, string $key) => $setting->hasSecret($key)
            ? 'Tersimpan. Kosongkan untuk tetap memakai nilai lama.'
            : 'Belum diisi.';

        return [
            Section::make('Email (SMTP)')
                ->icon('heroicon-o-envelope')
                ->statePath('email')
                ->columns(2)
                ->schema([
                    Toggle::make('is_enabled')->label('Aktifkan notifikasi email')->live()->columnSpanFull(),
                    TextInput::make('host')->label('Host SMTP')->required(fn (Get $get) => $get('is_enabled'))->maxLength(255),
                    TextInput::make('port')->label('Port')->numeric()->integer()->minValue(1)->maxValue(65535)->required(fn (Get $get) => $get('is_enabled')),
                    Select::make('encryption')->label('Enkripsi')->options(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Tanpa enkripsi'])->required(),
                    TextInput::make('username')->label('Username')->maxLength(255)->autocomplete('off'),
                    TextInput::make('password')->label('Password')->password()->revealable()->autocomplete('new-password')
                        ->helperText($secretHint($email, 'password')),
                    TextInput::make('from_address')->label('Alamat pengirim')->email()->required(fn (Get $get) => $get('is_enabled')),
                    TextInput::make('from_name')->label('Nama pengirim')->maxLength(255),
                ]),
            Section::make('WhatsApp')
                ->icon('heroicon-o-device-phone-mobile')
                ->statePath('whatsapp')
                ->columns(2)
                ->schema([
                    Toggle::make('is_enabled')->label('Aktifkan notifikasi WhatsApp')->live()->columnSpanFull(),
                    Select::make('provider')->label('Gateway')
                        ->options(WhatsAppGateways::PROVIDERS)
                        ->default('custom')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(fn (Get $get) => WhatsAppGateways::DESCRIPTIONS[$get('provider') ?: 'custom'] ?? null)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($url = WhatsAppGateways::DEFAULT_URLS[$state] ?? null) {
                                $set('api_url', $url);
                            }
                        })
                        ->columnSpanFull(),
                    TextInput::make('api_url')->label('URL API gateway')->url()->required(fn (Get $get) => $get('is_enabled'))->columnSpanFull(),
                    TextInput::make('api_token')->label('Token API')->password()->revealable()->autocomplete('new-password')
                        ->helperText($secretHint($whatsapp, 'api_token')),
                    TextInput::make('sender_id')->label('Nomor / ID pengirim')->maxLength(50)
                        ->required(fn (Get $get) => $get('is_enabled') && WhatsAppGateways::needsSender($get('provider')))
                        ->visible(fn (Get $get) => WhatsAppGateways::needsSender($get('provider'))),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Uji email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->modalDescription('Mengirim email uji memakai pengaturan yang sudah disimpan pada jalur yang dipilih.')
                ->form([
                    self::lineField(),
                    TextInput::make('recipient')->label('Kirim ke')->email()->required()->default(fn () => auth()->user()->email),
                ])
                ->action(fn (array $data) => self::report(
                    app(NotificationGateway::class)->testEmail($data['recipient'], $data['line']),
                    'Email uji terkirim ke ' . $data['recipient'] . '.',
                )),
            Action::make('testWhatsApp')
                ->label('Uji WhatsApp')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('gray')
                ->modalDescription('Mengirim pesan uji memakai pengaturan yang sudah disimpan pada jalur yang dipilih.')
                ->form([
                    self::lineField(),
                    TextInput::make('number')->label('Nomor tujuan')->tel()->required()
                        ->placeholder('628xxxxxxxxxx')
                        ->default(fn () => auth()->user()->whatsapp_number),
                ])
                ->action(fn (array $data) => self::report(
                    app(NotificationGateway::class)->testWhatsApp($data['number'], $data['line']),
                    'Pesan uji terkirim ke ' . $data['number'] . '.',
                )),
        ];
    }

    /** Which line's gateway a test send goes through. */
    private static function lineField(): Radio
    {
        return Radio::make('line')->label('Jalur')
            ->options(collect(NotificationSetting::LINES)->map(fn (string $label) => 'Jalur ' . $label)->all())
            ->default('pelayanan')
            ->required();
    }

    /** Show the result of a test send. */
    private static function report(?string $error, string $success): void
    {
        $error === null
            ? Notification::make()->title($success)->success()->send()
            : Notification::make()->title('Pengiriman uji gagal')->body($error)->danger()->persistent()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')->label('Simpan pengaturan')->submit('submit'),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        foreach (array_keys(NotificationSetting::LINES) as $line) {
            foreach (array_keys(NotificationSetting::CHANNELS) as $channel) {
                NotificationSetting::store($channel, (bool) $data[$line][$channel]['is_enabled'], $data[$line][$channel], auth()->id(), $line);
            }
        }

        $this->form->fill(collect($this->data)->map(fn ($channels) => collect($channels)->map(fn ($channel) => collect($channel)->except(NotificationSetting::SECRETS)->all())->all())->all());

        Notification::make()->title('Pengaturan integrasi disimpan.')->success()->send();
    }
}
