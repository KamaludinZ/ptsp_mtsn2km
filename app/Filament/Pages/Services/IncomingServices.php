<?php

namespace App\Filament\Pages\Services;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\IncomingCategory;
use App\Support\ProcessorRoles;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * Layanan masuk berkategori: requests that reached the back office, grouped
 * into disposisi, tembusan, koordinasi and arahan (one tab each). Staff
 * holding a processor role (Waka, TU, penjamin mutu) start with the
 * requests sent to their own units.
 */
class IncomingServices extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Back Office';

    protected static ?string $navigationLabel = 'Layanan Masuk';

    protected static ?string $title = 'Layanan Masuk';

    protected static ?string $slug = 'layanan-masuk';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.incoming-services';

    #[Url(as: 'kategori')]
    public ?string $activeTab = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('backoffice.access');
    }

    public static function getNavigationBadge(): ?string
    {
        $units = ProcessorRoles::unitsOf(auth()->user());
        $count = ($units ? ProcessorRoles::scopeTicketsFor(static::incomingQuery(), $units) : static::incomingQuery())->count();

        return $count ? (string) $count : null;
    }

    /** Open requests ready for the back office: disposed, or needing no disposition. */
    public static function incomingQuery(): Builder
    {
        return Ticket::query()
            ->whereIn('status', Ticket::OPEN_STATUSES)
            ->where(fn (Builder $q) => $q->where('approval_required', false)
                ->orWhereNull('approval_required')
                ->orWhere('approval_status', 'approved'));
    }

    public function getSubheading(): ?string
    {
        $units = ProcessorRoles::unitsOf(auth()->user());

        return 'Permohonan yang sudah sampai ke back office, dikelompokkan menurut instruksi disposisi pimpinan.'
            . ($units ? ' Unit Anda: ' . ServiceDisposition::recipients($units) . '.' : '');
    }

    /** @return array<string, array{label: string, count: int}> */
    public function getTabs(): array
    {
        $tabs = ['' => ['label' => 'Semua', 'count' => static::incomingQuery()->count()]];

        foreach (IncomingCategory::CATEGORIES as $category => $label) {
            $tabs[$category] = ['label' => $label, 'count' => IncomingCategory::scope(static::incomingQuery(), $category)->count()];
        }

        return $tabs;
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => static::incomingQuery()
                ->with(['service:id,name', 'user:id,name', 'approver:id,name'])
                ->when(
                    array_key_exists((string) $this->activeTab, IncomingCategory::CATEGORIES),
                    fn (Builder $q) => IncomingCategory::scope($q, $this->activeTab),
                ))
            ->defaultSort('approved_at', 'desc')
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold')->searchable()
                    ->description(fn (Ticket $record) => $record->isOverdue() ? 'Melewati target' : null),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge()
                    ->state(fn (Ticket $record) => IncomingCategory::of($record))
                    ->formatStateUsing(fn (string $state) => IncomingCategory::label($state))
                    ->color(fn (string $state) => IncomingCategory::COLORS[$state] ?? 'gray')
                    ->visible(fn () => blank($this->activeTab)),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable()->placeholder('–'),
                Tables\Columns\TextColumn::make('approval_notes')->label('Instruksi pimpinan')->wrap()->limit(120)
                    ->description(fn (Ticket $record) => $record->approver ? 'oleh ' . $record->approver->name : null)
                    ->placeholder('Tanpa disposisi'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('approved_at')->label('Masuk')->dateTime('d M Y H:i')->sortable()
                    ->placeholder('–'),
            ])
            ->filters([
                Tables\Filters\Filter::make('mine')->label('Hanya untuk unit saya')->toggle()
                    ->default(fn () => filled(ProcessorRoles::unitsOf(auth()->user())))
                    ->visible(fn () => filled(ProcessorRoles::unitsOf(auth()->user())))
                    ->query(fn (Builder $query) => ProcessorRoles::scopeTicketsFor($query, ProcessorRoles::unitsOf(auth()->user()))),
                Tables\Filters\SelectFilter::make('service_id')->label('Layanan')
                    ->relationship('service', 'name')->searchable()->preload(),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::can('view', $record) ? TicketResource::getUrl('view', ['record' => $record]) : null)
            ->emptyStateHeading(blank($this->activeTab) ? 'Belum ada layanan masuk' : 'Tidak ada layanan ' . mb_strtolower(IncomingCategory::label($this->activeTab)))
            ->emptyStateDescription(IncomingCategory::DESCRIPTIONS[$this->activeTab] ?? null)
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
