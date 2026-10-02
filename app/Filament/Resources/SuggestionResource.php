<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestionResource\Pages;
use App\Models\Complaint;
use App\Support\RoleAccess;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Suggestions (saran) sent from the public complaints page. Read by the
 * school leadership; unlike complaints they need no follow-up workflow,
 * only "new" vs "read".
 */
class SuggestionResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $slug = 'saran';

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $navigationLabel = 'Saran';

    protected static ?string $modelLabel = 'saran';

    protected static ?string $pluralModelLabel = 'Saran';

    protected static ?int $navigationSort = 2;

    public const STATUSES = ['submitted' => 'Baru', 'closed' => 'Sudah dibaca'];

    public static function can(string $action, ?Model $record = null): bool
    {
        return in_array($action, ['viewAny', 'view', 'update', 'delete', 'deleteAny'], true)
            && (bool) auth()->user()?->hasAnyRole(RoleAccess::LEADERSHIP);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('complaint_type', 'suggestion');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'submitted')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Saran baru yang belum dibaca';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Masuk')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Saran')->searchable()->wrap()->limit(120),
                Tables\Columns\TextColumn::make('reporter_name')->label('Pengirim')->searchable()->placeholder('Tanpa nama'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => static::STATUSES[$state] ?? 'Sudah dibaca')
                    ->color(fn (?string $state) => $state === 'submitted' ? 'warning' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(static::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Baca')
                    ->after(fn (Complaint $record) => static::markRead($record)),
                Tables\Actions\Action::make('markRead')
                    ->label('Tandai dibaca')
                    ->icon('heroicon-m-check')
                    ->color('gray')
                    ->visible(fn (Complaint $record) => $record->status === 'submitted')
                    ->action(fn (Complaint $record) => static::markRead($record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markRead')
                    ->label('Tandai dibaca')
                    ->icon('heroicon-m-check')
                    ->action(fn ($records) => $records->each(fn (Complaint $record) => static::markRead($record)))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Belum ada saran')
            ->emptyStateIcon('heroicon-o-light-bulb');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('description')->label('Saran')->columnSpanFull()->prose(),
            TextEntry::make('reporter_name')->label('Pengirim')->placeholder('Tanpa nama'),
            TextEntry::make('reporter_email')->label('Email')->placeholder('–')->copyable(),
            TextEntry::make('complaint_number')->label('No. Saran'),
            TextEntry::make('created_at')->label('Masuk')->dateTime('d M Y H:i'),
        ]);
    }

    public static function markRead(Complaint $record): void
    {
        if ($record->status === 'submitted') {
            $record->update(['status' => 'closed']);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuggestions::route('/'),
        ];
    }
}
