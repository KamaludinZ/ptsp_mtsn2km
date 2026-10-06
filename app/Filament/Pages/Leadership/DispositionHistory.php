<?php

namespace App\Filament\Pages\Leadership;

use App\Filament\Resources\TicketResource;
use App\Models\TicketLog;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use App\Support\ServiceDisposition;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Riwayat disposisi: every disposition and rejection by the school
 * leadership, with the signature model used. Read-only.
 */
class DispositionHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Riwayat Disposisi';

    protected static ?string $title = 'Riwayat Disposisi';

    protected static ?string $slug = 'pimpinan/riwayat-disposisi';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasAnyRole(RoleAccess::LEADERSHIP) || $user->can('supervision.access'));
    }

    /** In the menu for the active role: a leadership role, or one that supervises. */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && (ActiveRoles::actsAsLeader($user) || ActiveRoles::can($user, 'supervision.access'));
    }

    public static function getNavigationGroup(): ?string
    {
        return ActiveRoles::actsAsLeader(auth()->user()) ? 'Pimpinan' : 'Pengawasan';
    }

    public function getSubheading(): ?string
    {
        return 'Seluruh disposisi dan penolakan pimpinan beserta model tanda tangannya. Riwayat tidak dapat diubah.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TicketLog::query()
                ->whereIn('action', ['approved', 'rejected'])
                ->with(['ticket:id,ticket_number,service_id,signature_type', 'ticket.service:id,name', 'performer:id,name']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('ticket.ticket_number')->label('No. Tiket')->weight('semibold')->searchable()
                    ->url(fn (TicketLog $record) => $record->ticket && TicketResource::can('view', $record->ticket)
                        ? TicketResource::getUrl('view', ['record' => $record->ticket])
                        : null),
                Tables\Columns\TextColumn::make('ticket.service.name')->label('Layanan')->wrap()->placeholder('–'),
                Tables\Columns\TextColumn::make('performer.name')->label('Pejabat')->searchable()->placeholder('Sistem'),
                Tables\Columns\TextColumn::make('action')->label('Keputusan')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'approved' ? 'Didisposisi' : 'Ditolak')
                    ->color(fn (string $state) => $state === 'approved' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('ticket.signature_type')->label('Tanda tangan')
                    ->formatStateUsing(fn (?string $state, TicketLog $record) => $record->action === 'approved' ? ServiceDisposition::signatureTypeLabel($state) : null)
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('notes')->label('Instruksi / catatan')->searchable()->wrap()->limit(160)->placeholder('–'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')->label('Keputusan')
                    ->options(['approved' => 'Didisposisi', 'rejected' => 'Ditolak']),
                Tables\Filters\SelectFilter::make('performed_by')->label('Pejabat')
                    ->options(fn () => User::role(RoleAccess::LEADERSHIP)->orderBy('name')->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('signature')->label('Model tanda tangan')
                    ->options(array_combine(ServiceDisposition::SIGNATURE_TYPES, array_map(
                        fn (string $type) => ServiceDisposition::signatureTypeLabel($type),
                        ServiceDisposition::SIGNATURE_TYPES,
                    )))
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $q, $type) => $q->where('action', 'approved')->whereHas('ticket', fn (Builder $t) => $t->where('signature_type', $type)),
                    )),
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->emptyStateHeading('Belum ada disposisi')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
