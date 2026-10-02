<?php

namespace App\Filament\Pages\System;

use App\Models\NotificationSetting;
use App\Services\NotificationGateway;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Pengaturan integrasi Email & WhatsApp: gateway configuration and an
 * on/off switch per channel. Secrets are stored encrypted and never sent
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

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public function getSubheading(): ?string
    {
        return 'Gateway pengiriman notifikasi ke pemohon dan petugas. Kanal yang dinonaktifkan tidak mengirim pesan apa pun.';
    }

    public function mount(): void
    {
        $email = NotificationSetting::for('email');
        $whatsapp = NotificationSetting::for('whatsapp');

        // Unsaved channels start from the .env configuration.
        $this->form->fill([
            'email' => [
                'is_enabled' => $email->is_enabled,
                'host' => $email->value('host', config('mail.mailers.smtp.host')),
                'port' => $email->value('port', config('mail.mailers.smtp.port')),
                'encryption' => $email->value('encryption', config('mail.mailers.smtp.encryption') ?: 'none'),
                'username' => $email->value('username', config('mail.mailers.smtp.username')),
                'from_address' => $email->value('from_address', config('mail.from.address')),
                'from_name' => $email->value('from_name', config('mail.from.name')),
            ],
            'whatsapp' => [
                'is_enabled' => $whatsapp->is_enabled,
                'api_url' => $whatsapp->value('api_url', config('whatsapp.api_url')),
                'sender_id' => $whatsapp->value('sender_id', config('whatsapp.sender_id')),
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        $email = NotificationSetting::for('email');
        $whatsapp = NotificationSetting::for('whatsapp');
        $secretHint = fn (NotificationSetting $setting, string $key) => $setting->hasSecret($key)
            ? 'Tersimpan. Kosongkan untuk tetap memakai nilai lama.'
            : 'Belum diisi.';

        return $form
            ->statePath('data')
            ->schema([
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
                        TextInput::make('api_url')->label('URL API gateway')->url()->required(fn (Get $get) => $get('is_enabled'))->columnSpanFull(),
                        TextInput::make('api_token')->label('Token API')->password()->revealable()->autocomplete('new-password')
                            ->helperText($secretHint($whatsapp, 'api_token')),
                        TextInput::make('sender_id')->label('Nomor / ID pengirim')->required(fn (Get $get) => $get('is_enabled'))->maxLength(50),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Uji email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->modalDescription('Mengirim email uji memakai pengaturan yang sudah disimpan.')
                ->form([
                    TextInput::make('recipient')->label('Kirim ke')->email()->required()->default(fn () => auth()->user()->email),
                ])
                ->action(fn (array $data) => self::report(
                    app(NotificationGateway::class)->testEmail($data['recipient']),
                    'Email uji terkirim ke ' . $data['recipient'] . '.',
                )),
            Action::make('testWhatsApp')
                ->label('Uji WhatsApp')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('gray')
                ->modalDescription('Mengirim pesan uji memakai pengaturan yang sudah disimpan.')
                ->form([
                    TextInput::make('number')->label('Nomor tujuan')->tel()->required()
                        ->placeholder('628xxxxxxxxxx')
                        ->default(fn () => auth()->user()->whatsapp_number),
                ])
                ->action(fn (array $data) => self::report(
                    app(NotificationGateway::class)->testWhatsApp($data['number']),
                    'Pesan uji terkirim ke ' . $data['number'] . '.',
                )),
        ];
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

        foreach (array_keys(NotificationSetting::CHANNELS) as $channel) {
            $setting = NotificationSetting::for($channel);
            $values = collect($data[$channel])->except('is_enabled');

            $config = array_merge(
                $setting->config ?? [],
                $values->except(NotificationSetting::SECRETS)->all(),
                // A blank secret keeps the stored one.
                $values->only(NotificationSetting::SECRETS)->filter(fn ($value) => filled($value))->all(),
            );

            $setting->fill([
                'is_enabled' => (bool) $data[$channel]['is_enabled'],
                'config' => $config,
                'updated_by' => auth()->id(),
            ])->save();
        }

        $this->form->fill(collect($this->data)->map(fn ($channel) => collect($channel)->except(NotificationSetting::SECRETS)->all())->all());

        Notification::make()->title('Pengaturan integrasi disimpan.')->success()->send();
    }
}
