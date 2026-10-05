<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationDeliveryResource\Pages;
use App\Models\NotificationDelivery;
use App\Services\NotificationDispatcher;
use App\Support\NotificationTemplates;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Riwayat Notifikasi (admin): every e-mail / WhatsApp message sent for a
 * ticket, whether it arrived, and why it failed; failed ones can be resent.
 */
class NotificationDeliveryResource extends Resource
{
    protected static ?string $model = NotificationDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Riwayat Notifikasi';

    protected static ?string $modelLabel = 'notifikasi terkirim';

    protected static ?string $pluralModelLabel = 'Riwayat Notifikasi';

    protected static ?string $slug = 'riwayat-notifikasi';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $failed = NotificationDelivery::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count();

        return $failed ? (string) $failed : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Notifikasi gagal dalam 7 hari terakhir';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ticket:id,ticket_number', 'user:id,name']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable()
                    ->description(fn (NotificationDelivery $record) => $record->created_at?->diffForHumans()),
                Tables\Columns\TextColumn::make('event')->label('Pemicu')
                    ->formatStateUsing(fn (string $state) => NotificationTemplates::EVENTS[$state] ?? $state)
                    ->wrap(),
                Tables\Columns\TextColumn::make('channel')->label('Kanal')->badge()
                    ->formatStateUsing(fn (string $state) => NotificationDelivery::CHANNELS[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'whatsapp' ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('user.name')->label('Penerima')->placeholder('–')
                    ->description(fn (NotificationDelivery $record) => $record->recipient)
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $q) => $q
                        ->where('recipient', 'ilike', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'ilike', "%{$search}%")))),
                Tables\Columns\TextColumn::make('ticket.ticket_number')->label('Tiket')->placeholder('–')
                    ->url(fn (NotificationDelivery $record) => $record->ticket ? TicketResource::getUrl('view', ['record' => $record->ticket]) : null)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => NotificationDelivery::STATUSES[$state] ?? $state)
                    ->color(fn (NotificationDelivery $record) => $record->statusColor())
                    ->description(fn (NotificationDelivery $record) => $record->status === 'failed' ? mb_strimwidth((string) $record->error, 0, 80, '…') : null)
                    ->tooltip(fn (NotificationDelivery $record) => $record->error),
                Tables\Columns\TextColumn::make('attempts')->label('Percobaan')->alignCenter()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(NotificationDelivery::STATUSES),
                Tables\Filters\SelectFilter::make('channel')->label('Kanal')->options(NotificationDelivery::CHANNELS),
                Tables\Filters\SelectFilter::make('event')->label('Pemicu')->options(NotificationTemplates::EVENTS),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Isi pesan'),
                Tables\Actions\Action::make('resend')
                    ->label('Kirim ulang')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription(fn (NotificationDelivery $record) => 'Pesan yang sama dikirim lagi ke ' . $record->recipient . '.')
                    ->visible(fn (NotificationDelivery $record) => $record->status === 'failed' && in_array($record->channel, ['email', 'whatsapp'], true))
                    ->action(function (NotificationDelivery $record) {
                        $sent = app(NotificationDispatcher::class)->deliver($record);
                        Notification::make()
                            ->title($sent ? 'Notifikasi terkirim' : 'Masih gagal terkirim')
                            ->body($sent ? null : $record->error)
                            ->{$sent ? 'success' : 'danger'}()
                            ->send();
                    }),
            ])
            ->emptyStateIcon('heroicon-o-paper-airplane')
            ->emptyStateHeading('Belum ada notifikasi terkirim')
            ->emptyStateDescription('Pesan Email dan WhatsApp yang dikirim untuk permohonan akan tercatat di sini beserta statusnya.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('event')->label('Pemicu')->formatStateUsing(fn (string $state) => NotificationTemplates::EVENTS[$state] ?? $state),
            TextEntry::make('channel')->label('Kanal')->formatStateUsing(fn (string $state) => NotificationDelivery::CHANNELS[$state] ?? $state),
            TextEntry::make('recipient')->label('Dikirim ke'),
            TextEntry::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state) => NotificationDelivery::STATUSES[$state] ?? $state)
                ->color(fn (NotificationDelivery $record) => $record->statusColor()),
            TextEntry::make('error')->label('Penyebab gagal')->placeholder('–')->columnSpanFull()
                ->visible(fn (NotificationDelivery $record) => $record->status === 'failed'),
            TextEntry::make('subject')->label('Subjek')->placeholder('–')->columnSpanFull(),
            TextEntry::make('body')->label('Isi pesan')->columnSpanFull()->extraAttributes(['style' => 'white-space: pre-line']),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationDeliveries::route('/'),
        ];
    }
}
