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
            $this->getPasswordFormComponent()->label('Kata sandi baru'),
            $this->getPasswordConfirmationFormComponent()->label('Ulangi kata sandi baru'),
        ]);
    }

    /** A changed e-mail address has to be verified again (applicants cannot work unverified). */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->fill($data);

        if ($record->isDirty('email')) {
            $record->email_verified_at = null;
        }

        $record->save();

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
