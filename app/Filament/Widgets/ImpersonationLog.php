<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\System\Impersonation;
use App\Models\ImpersonationSession;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/** Riwayat ganti akun di bawah halaman Ganti Akun Sementara (administrator saja). */
class ImpersonationLog extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Riwayat ganti akun';

    public static function canView(): bool
    {
        return Impersonation::canAccess();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ImpersonationSession::query())
            ->defaultSort('started_at', 'desc')
            ->paginated([10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('started_at')->label('Mulai')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('admin_name')->label('Administrator')->searchable(),
                Tables\Columns\TextColumn::make('target_name')->label('Akun dipakai')->searchable()
                    ->description(fn (ImpersonationSession $s) => $s->target_role ? \App\Support\RoleAccess::roleLabel($s->target_role) : null),
                Tables\Columns\TextColumn::make('reason')->label('Alasan')->wrap()->limit(120)->searchable(),
                Tables\Columns\TextColumn::make('end_reason')->label('Berakhir')->badge()
                    ->state(fn (ImpersonationSession $s) => $s->isOpen() ? 'berjalan' : $s->end_reason)
                    ->formatStateUsing(fn (?string $state) => $state === 'berjalan' ? 'Masih berjalan' : (ImpersonationSession::END_REASONS[$state] ?? $state))
                    ->color(fn (?string $state) => match ($state) { 'berjalan' => 'warning', 'selesai' => 'success', default => 'gray' })
                    ->description(fn (ImpersonationSession $s) => $s->ended_at ? (int) $s->started_at->diffInMinutes($s->ended_at) . ' menit' : null),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('admin_id')->label('Administrator')
                    ->options(fn () => ImpersonationSession::query()->whereNotNull('admin_id')->distinct()->pluck('admin_name', 'admin_id')->all()),
                Tables\Filters\SelectFilter::make('status')->label('Status')
                    ->options(['berjalan' => 'Masih berjalan', 'selesai' => 'Sudah berakhir'])
                    ->query(fn (Builder $query, array $data) => $query->filtered(['status' => $data['value'] ?? null])),
                Tables\Filters\SelectFilter::make('end_reason')->label('Cara berakhir')->options(ImpersonationSession::END_REASONS),
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->filtered(['dari' => $data['from'] ?? null, 'sampai' => $data['until'] ?? null])),
            ])
            ->emptyStateHeading('Belum ada sesi ganti akun')
            ->emptyStateIcon('heroicon-o-user-circle');
    }
}
