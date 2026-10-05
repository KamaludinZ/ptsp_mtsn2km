<?php

namespace App\Filament\Shared\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Account settings in both panels: name, e-mail, WhatsApp number and
 * password. Applicants may also delete their own account.
 */
class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getNameFormComponent()->label('Nama lengkap'),
            $this->getEmailFormComponent()->label('Email'),
            TextInput::make('whatsapp_number')
                ->label('Nomor WhatsApp')
                ->tel()
                ->maxLength(20)
                ->helperText('Dipakai untuk mengirim kabar tentang permohonan Anda.'),
            \Filament\Forms\Components\Section::make('Preferensi tampilan')
                ->description('Berlaku di panel ini untuk akun Anda, di perangkat mana pun.')
                ->columns(2)
                ->schema([
                    \Filament\Forms\Components\Radio::make('display_mode')
                        ->label('Mode tampilan')
                        ->options(\App\Support\DisplayPreferences::MODES)
                        ->default('system'),
                    \Filament\Forms\Components\Radio::make('display_size')
                        ->label('Ukuran huruf')
                        ->options(\App\Support\DisplayPreferences::SIZES)
                        ->default('normal'),
                ]),
            \Filament\Forms\Components\Section::make('Ganti kata sandi')
                ->description('Kosongkan bila kata sandi tidak diubah.')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Kata sandi saat ini')
                        ->password()
                        ->revealable()
                        ->autocomplete('current-password')
                        ->currentPassword()
                        ->required(fn (\Filament\Forms\Get $get) => filled($get('password')))
                        ->validationMessages(['current_password' => 'Kata sandi saat ini tidak cocok.'])
                        ->dehydrated(false),
                    $this->getPasswordFormComponent()
                        ->label('Kata sandi baru')
                        ->revealable()
                        ->rule(\Illuminate\Validation\Rules\Password::min(8)->letters()->numbers())
                        ->helperText('Minimal 8 karakter, berisi huruf dan angka.'),
                    $this->getPasswordConfirmationFormComponent()->label('Ulangi kata sandi baru')->revealable(),
                ]),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $preferences = \App\Support\DisplayPreferences::for($this->getUser());

        return $data + ['display_mode' => $preferences['mode'], 'display_size' => $preferences['size']];
    }

    /** A changed e-mail address has to be verified again (applicants cannot work unverified). */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $current = \App\Support\DisplayPreferences::for($record);
        $mode = $data['display_mode'] ?? $current['mode'];
        $size = $data['display_size'] ?? $current['size'];
        unset($data['display_mode'], $data['display_size']);
        if ($mode !== $current['mode'] || $size !== $current['size']) {
            \App\Support\DisplayPreferences::save($record, $mode, $size);
        }

        $record->fill($data);

        if ($record->isDirty('email')) {
            $record->email_verified_at = null;
        }

        $record->save();

        if ($record->wasChanged('password')) {
            activity('audit')->causedBy($record)->performedOn($record)->log('Mengganti kata sandi');
        }

        if ($record->wasChanged('email') && $record instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $record->sendEmailVerificationNotification();
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteAccount')
                ->label('Hapus akun')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->visible(fn () => ! Auth::user()->isStaff())
                ->requiresConfirmation()
                ->modalHeading('Hapus akun Anda?')
                ->modalDescription('Akun dan akses ke riwayat permohonan akan dihapus. Tindakan ini tidak dapat dibatalkan.')
                ->form([
                    TextInput::make('password')
                        ->label('Kata sandi')
                        ->password()
                        ->required()
                        ->currentPassword(),
                ])
                ->action(function () {
                    $user = Auth::user();
                    Auth::logout();
                    $user->delete();
                    session()->invalidate();
                    session()->regenerateToken();

                    return redirect()->route('home');
                }),
        ];
    }
}
