<?php

namespace App\Filament\Pages\Leadership;

use App\Filament\Concerns\NotifiesActionResult;
use App\Filament\Forms\DispositionForm;
use App\Filament\Resources\TicketResource;
use App\Filament\Widgets\Leadership\DecisionHistory;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Support\CivitasRegistration;
use App\Support\RoleAccess;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * Antrean disposisi masuk: requests verified by the back office that wait
 * for this leader's disposition (formerly "persetujuan pimpinan", Modul 8).
 * Only tickets the leader may decide on are listed.
 */
class Approvals extends Page implements HasTable
{
    use InteractsWithTable;
    use NotifiesActionResult;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Pimpinan';

    protected static ?string $navigationLabel = 'Disposisi Masuk';

    protected static ?string $title = 'Antrean Disposisi';

    protected static ?string $slug = 'pimpinan/disposisi';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(RoleAccess::LEADERSHIP);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Ticket::approvableBy(auth()->user())->count();

        return $count ? (string) $count : null;
    }

    public function getSubheading(): ?string
    {
        return 'Permohonan yang berkasnya sudah diverifikasi petugas TU dan menunggu disposisi Anda.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()
                ->with(['service:id,name,signature_recommendation,approval_required,approval_roles,approval_users', 'user:id,name,user_type'])
                ->whereIn('id', Ticket::approvableBy(auth()->user())->pluck('id')))
            ->defaultSort('created_at')
            ->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold')->searchable()
                    ->description(fn (Ticket $record) => $record->isOverdue() ? 'Melewati target' : null),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap()
                    ->description(fn (Ticket $record) => $record->service ? 'Disposisi: ' . ServiceDisposition::mode($record->service->disposition_mode) : null),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable()
                    ->description(fn (Ticket $record) => CivitasRegistration::USER_TYPES[$record->user?->user_type] ?? ($record->user?->user_type === 'umum' ? 'Umum' : null)),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('service.signature_recommendation')->label('Anjuran TTD/TTE')
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->badge()->color('gray')->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->since()->sortable(),
                Tables\Columns\TextColumn::make('estimated_completion_date')->label('Target selesai')->date('d M Y')
                    ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null)
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Disposisikan')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->modalHeading(fn (Ticket $record) => 'Disposisi · ' . $record->ticket_number)
                    ->form(fn (Ticket $record) => DispositionForm::schema($record))
                    ->action(fn (Ticket $record, array $data) => self::attempt(
                        fn () => DispositionForm::submit($record, $data),
                        "Tiket {$record->ticket_number} telah didisposisi.",
                    )),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('notes')->label('Alasan penolakan')->required()->maxLength(500)->rows(3),
                    ])
                    ->action(fn (Ticket $record, array $data) => self::attempt(
                        fn () => app(TicketService::class)->decide($record, false, auth()->user(), null, $data['notes']),
                        "Tiket {$record->ticket_number} ditolak.",
                    )),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Tidak ada disposisi yang menunggu')
            ->emptyStateDescription('Permohonan muncul di sini setelah berkasnya diverifikasi petugas TU.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getFooterWidgets(): array
    {
        return [DecisionHistory::class];
    }
}
