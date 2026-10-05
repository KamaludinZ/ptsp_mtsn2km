<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Url;

/**
 * Halaman Notifikasi: the signed-in user's in-app notifications (the same
 * ones as the bell), with read/unread tabs, open-and-mark-read, mark all
 * read and delete. Shared by the staff panel and the applicant portal.
 */
trait NotificationInbox
{
    #[Url(as: 'tampil')]
    public string $show = 'semua';

    public function getSubheading(): ?string
    {
        $unread = auth()->user()->unreadNotifications()->count();

        return $unread ? "{$unread} notifikasi belum dibaca." : 'Semua notifikasi sudah dibaca.';
    }

    public function updatedShow(): void
    {
        $this->resetPage();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => \App\Models\Notification::query()
                ->whereMorphedTo('notifiable', auth()->user())
                ->when($this->show === 'belum-dibaca', fn (Builder $q) => $q->whereNull('read_at')))
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->columns([
                Tables\Columns\IconColumn::make('read_at')
                    ->label('')
                    ->icon(fn (DatabaseNotification $record) => $record->read_at ? 'heroicon-o-envelope-open' : 'heroicon-s-envelope')
                    ->color(fn (DatabaseNotification $record) => $record->read_at ? 'gray' : 'primary')
                    ->tooltip(fn (DatabaseNotification $record) => $record->read_at ? 'Sudah dibaca' : 'Belum dibaca')
                    ->width('1%'),
                Tables\Columns\TextColumn::make('data.title')
                    ->label('Notifikasi')
                    ->weight(fn (DatabaseNotification $record) => $record->read_at ? 'normal' : 'bold')
                    ->description(fn (DatabaseNotification $record) => strip_tags((string) ($record->data['body'] ?? '')) ?: null)
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereRaw('lower(data::text) like ?', ['%' . mb_strtolower($search) . '%'])),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since()
                    ->tooltip(fn (DatabaseNotification $record) => $record->created_at?->translatedFormat('j F Y, H:i'))
                    ->sortable(),
            ])
            ->recordAction('open')
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->action(function (DatabaseNotification $record) {
                        $record->markAsRead();
                        if ($url = self::urlOf($record)) {
                            $this->redirect($url);
                        }
                    })
                    ->visible(fn (DatabaseNotification $record) => (bool) self::urlOf($record)),
                Tables\Actions\Action::make('toggleRead')
                    ->label(fn (DatabaseNotification $record) => $record->read_at ? 'Tandai belum dibaca' : 'Tandai dibaca')
                    ->icon(fn (DatabaseNotification $record) => $record->read_at ? 'heroicon-m-envelope' : 'heroicon-m-envelope-open')
                    ->color('gray')
                    ->action(fn (DatabaseNotification $record) => $record->read_at ? $record->markAsUnread() : $record->markAsRead()),
                Tables\Actions\DeleteAction::make()->label('Hapus')->modalHeading('Hapus notifikasi?'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('markAllRead')
                    ->label('Tandai semua dibaca')
                    ->icon('heroicon-m-check')
                    ->color('gray')
                    ->visible(fn () => auth()->user()->unreadNotifications()->exists())
                    ->action(function () {
                        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
                        FilamentNotification::make()->title('Semua notifikasi ditandai dibaca')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markRead')->label('Tandai dibaca')->icon('heroicon-m-envelope-open')
                    ->action(fn ($records) => $records->each->markAsRead())->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-bell-slash')
            ->emptyStateHeading($this->show === 'belum-dibaca' ? 'Tidak ada notifikasi belum dibaca' : 'Belum ada notifikasi')
            ->emptyStateDescription($this->show === 'belum-dibaca'
                ? 'Semua notifikasi Anda sudah dibaca.'
                : 'Pemberitahuan tentang permohonan, disposisi, dan perubahan status akan muncul di sini.');
    }

    /** @return array<string, array{label: string, count: ?int}> */
    public function getInboxTabs(): array
    {
        return [
            'semua' => ['label' => 'Semua', 'count' => null],
            'belum-dibaca' => ['label' => 'Belum dibaca', 'count' => auth()->user()->unreadNotifications()->count() ?: null],
        ];
    }

    /** The page a notification opens (its request, for this reader). */
    public static function urlOf(DatabaseNotification $notification): ?string
    {
        return \App\Support\TicketNotification::urlOf($notification, auth()->user());
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = auth()->user()?->unreadNotifications()->count();

        return $unread ? (string) $unread : null;
    }
}
