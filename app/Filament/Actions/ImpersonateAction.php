<?php

namespace App\Filament\Actions;

use App\Exceptions\ImpersonationException;
use App\Filament\Pages\System\Impersonation;
use App\Models\User;
use App\Support\Impersonation as ImpersonationSession;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;

/**
 * "Masuk sebagai": the same action (reason required, own and inactive
 * accounts refused) on Ganti Akun Sementara, the user list and a user's
 * page. Shown only to an administrator working as one.
 */
class ImpersonateAction
{
    public static function table(): TableAction
    {
        return self::configure(TableAction::make('impersonate'));
    }

    public static function page(): PageAction
    {
        return self::configure(PageAction::make('impersonate'));
    }

    /**
     * @template T of TableAction|PageAction
     *
     * @param  T  $action
     * @return T
     */
    private static function configure(TableAction|PageAction $action): TableAction|PageAction
    {
        return $action
            ->label('Masuk sebagai')
            ->icon('heroicon-m-arrow-right-end-on-rectangle')
            ->color('warning')
            ->visible(fn () => Impersonation::canAccess())
            ->disabled(fn (User $record) => Impersonation::blockedReason($record) !== null)
            ->tooltip(fn (User $record) => Impersonation::blockedReason($record))
            ->modalHeading(fn (User $record) => 'Masuk sebagai ' . $record->name)
            ->modalDescription('Anda akan melihat aplikasi persis seperti pengguna ini. Penanda di bagian atas layar mengingatkan bahwa Anda sedang memakai akun orang lain.')
            ->modalSubmitActionLabel('Mulai ganti akun')
            ->form([
                Textarea::make('reason')->label('Alasan')
                    ->placeholder('mis. Meninjau keluhan pemohon tentang unggah berkas')
                    ->required()->minLength(10)->maxLength(500)->rows(3)
                    ->helperText('Dicatat pada rekam jejak dan hanya dapat dilihat administrator.'),
            ])
            ->action(function (User $record, array $data, $livewire) {
                try {
                    ImpersonationSession::start(auth()->user(), $record, $data['reason']);
                } catch (ImpersonationException $e) {
                    Notification::make()->danger()->title('Tidak dapat ganti akun')->body($e->getMessage())->send();

                    return;
                }

                Notification::make()->warning()
                    ->title('Anda memakai akun ' . $record->name)
                    ->body('Sesi ini dicatat. Pilih "Kembali ke akun admin" bila sudah selesai.')
                    ->send();

                // A full page load: the other account has its own panel and menus.
                $livewire->redirect(get_dashboard_route_for_user($record));
            });
    }
}
