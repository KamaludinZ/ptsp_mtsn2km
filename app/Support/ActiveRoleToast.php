<?php

namespace App\Support;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Toast hasil perpindahan peran aktif, sama untuk halaman Pilih Peran
 * Aktif dan menu ganti peran di header. Toast ikut terbawa ke halaman
 * berikutnya (redirect), jadi aksi "Kembali ke ..." dikirim sebagai event
 * yang didengar menu di header (ActiveRoleSwitcher).
 */
class ActiveRoleToast
{
    public const UNDO_EVENT = 'switch-active-role';

    /**
     * Pindah ke $role (ActiveRoles::switchTo) dan tampilkan toast hasilnya.
     *
     * @return ?bool true bila berpindah, false bila peran itu sudah aktif, null bila ditolak
     */
    public static function switchTo(User $user, string $role): ?bool
    {
        try {
            $switch = ActiveRoles::switchTo($user, $role);
        } catch (ValidationException) {
            self::unavailable();

            return null;
        }

        if ($switch['from'] === $switch['to']) {
            self::alreadyActive(ActiveRoles::row($switch['to']));

            return false;
        }

        self::switched($switch['from'] ? ActiveRoles::row($switch['from']) : null, ActiveRoles::row($switch['to']));

        return true;
    }

    /**
     * @param  ?array{name: string, label: string}  $from  null bila sebelumnya belum ada peran aktif
     * @param  array{name: string, label: string}  $to
     */
    public static function switched(?array $from, array $to): void
    {
        $follow = "Menu dan tombol aksi kini mengikuti peran {$to['label']}.";

        Notification::make()->success()
            ->icon('heroicon-o-arrows-right-left')
            ->title('Peran aktif: ' . $to['label'])
            ->body($from ? "Berpindah dari {$from['label']}. {$follow}" : $follow)
            ->duration(8000)
            ->actions($from ? [
                Action::make('undo')
                    ->label('Kembali ke ' . $from['label'])
                    ->link()
                    ->color('gray')
                    ->dispatch(self::UNDO_EVENT, ['role' => $from['name']])
                    ->close(),
            ] : [])
            ->send();
    }

    /** Aksi ditolak karena di luar wewenang peran aktif, dengan tombol ganti ke peran yang berwenang. */
    public static function outsideRole(\App\Exceptions\OutsideActiveRoleException $e): void
    {
        Notification::make()->danger()
            ->icon('heroicon-o-lock-closed')
            ->title('Di luar wewenang peran aktif')
            ->body($e->getMessage())
            ->persistent()
            ->actions($e->suggestedRole ? [
                Action::make('switch')
                    ->label('Ganti ke ' . RoleAccess::roleLabel($e->suggestedRole))
                    ->button()
                    ->dispatch(self::UNDO_EVENT, ['role' => $e->suggestedRole])
                    ->close(),
            ] : [])
            ->send();
    }

    public static function alreadyActive(array $role): void
    {
        Notification::make()->info()
            ->title('Anda sudah memakai peran ' . $role['label'])
            ->send();
    }

    public static function unavailable(): void
    {
        Notification::make()->danger()
            ->title('Peran tidak tersedia untuk akun ini')
            ->body('Pilih salah satu peran yang Anda pegang.')
            ->send();
    }
}
