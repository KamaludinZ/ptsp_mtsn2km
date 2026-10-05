<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\IncomingCategory;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The service tickets every staff role works from (Modul 7-9): the back
 * office processes them, leaders decide approvals and the counter hands the
 * finished products over. What each role may do is decided per action.
 */
class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $slug = 'tiket';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Back Office';

    protected static ?string $navigationLabel = 'Tiket Layanan';

    protected static ?string $modelLabel = 'tiket';

    protected static ?string $pluralModelLabel = 'Tiket Layanan';

    protected static ?string $recordTitleAttribute = 'ticket_number';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('backoffice.access')) {
            return null;
        }

        $count = Ticket::open()->whereNull('assigned_to_id')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tiket aktif yang belum ditugaskan';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user:id,name,email,whatsapp_number', 'service:id,name', 'assignedTo:id,name']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['ticket_number', 'user.name'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->description(fn (Ticket $record) => $record->user?->whatsapp_number),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Jalur')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::mode($state))
                    ->color(fn (?string $state) => $state === 'offline' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->extraAttributes(['data-ticket-status' => true])
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::priority($state))
                    ->color(fn (?string $state) => TicketLabels::priorityColor($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kategori_masuk')
                    ->label('Kategori masuk')
                    ->badge()
                    ->state(fn (Ticket $record) => IncomingCategory::of($record))
                    ->formatStateUsing(fn (string $state) => IncomingCategory::label($state))
                    ->color(fn (string $state) => IncomingCategory::COLORS[$state] ?? 'gray')
                    ->tooltip(fn (Ticket $record) => $record->disposition_recipients ? 'Unit: ' . ServiceDisposition::recipients($record->disposition_recipients) : null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Petugas')
                    ->placeholder('Belum ditugaskan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_completion_date')
                    ->label('Target selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null)
                    ->icon(fn (Ticket $record) => $record->isOverdue() ? 'heroicon-m-exclamation-triangle' : null)
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(\App\Support\ServiceMetrics::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Jenis layanan')
                    ->multiple()
                    ->options(fn () => static::serviceOptions())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Jalur')
                    ->options(TicketLabels::MODES),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options(TicketLabels::PRIORITIES),
                Tables\Filters\SelectFilter::make('kategori_masuk')
                    ->label('Kategori layanan masuk')
                    ->options(IncomingCategory::CATEGORIES)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) ? IncomingCategory::scope($query, $data['value']) : $query),
                Tables\Filters\SelectFilter::make('unit')
                    ->label('Unit tujuan disposisi')
                    ->options(ServiceDisposition::RECIPIENTS)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) ? $query->forwardedTo([$data['value']]) : $query),
                Tables\Filters\SelectFilter::make('assigned_to_id')
                    ->label('Petugas')
                    ->options(fn () => User::role(User::STAFF_ROLES)->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('terlambat')
                    ->label('Melewati target')
                    ->queries(
                        true: fn (Builder $query) => $query->overdue(),
                        false: fn (Builder $query) => $query->whereNot(fn (Builder $q) => $q->overdue()),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal pengajuan')
                    ->form([
                        \Filament\Forms\Components\Select::make('period')
                            ->label('Tanggal pengajuan')
                            ->placeholder('Semua tanggal')
                            ->options(static::DATE_PERIODS + ['custom' => 'Pilih rentang…'])
                            ->live(),
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('period') === 'custom')
                            ->maxDate(fn (\Filament\Forms\Get $get) => $get('until') ?: today()),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('period') === 'custom')
                            ->minDate(fn (\Filament\Forms\Get $get) => $get('from')),
                    ])
                    ->columns(1)
                    ->query(function (Builder $query, array $data) {
                        [$from, $until] = static::dateRange($data);

                        return $query
                            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                            ->when($until, fn ($q) => $q->whereDate('created_at', '<=', $until));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        [$from, $until] = static::dateRange($data);
                        if (($data['period'] ?? null) && $data['period'] !== 'custom') {
                            return 'Diajukan: ' . static::DATE_PERIODS[$data['period']];
                        }

                        return match (true) {
                            $from && $until => 'Diajukan ' . $from->translatedFormat('j M Y') . ' – ' . $until->translatedFormat('j M Y'),
                            (bool) $from => 'Diajukan sejak ' . $from->translatedFormat('j M Y'),
                            (bool) $until => 'Diajukan sampai ' . $until->translatedFormat('j M Y'),
                            default => null,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make(static::quickStatusActions())
                    ->label('Status')
                    ->icon('heroicon-m-arrow-path')
                    ->tooltip('Ubah status cepat')
                    ->visible(fn (Ticket $record) => auth()->user()?->can('update', $record)
                        && app(TicketService::class)->nextStatuses($record, auth()->user()) !== []),
                Tables\Actions\ViewAction::make()->label('Buka'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->description(fn ($livewire) => static::narrowingSummary($livewire))
            ->headerActions([
                Tables\Actions\Action::make('resetAll')
                    ->label('Reset pencarian & filter')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('gray')
                    ->size('sm')
                    ->action(function ($livewire) {
                        $livewire->resetTableSearch();
                        $livewire->resetTableFiltersForm();
                    })
                    ->visible(fn ($livewire) => static::isNarrowed($livewire)),
            ])
            ->searchPlaceholder('Cari no. tiket, pemohon, WA, layanan…')
            ->searchDebounce('400ms')
            ->persistSearchInSession()
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 4])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->recordUrl(fn (Ticket $record) => static::getUrl('view', ['record' => $record]))
            ->emptyStateHeading(fn ($livewire) => static::emptyState($livewire)['heading'])
            ->emptyStateDescription(fn ($livewire) => static::emptyState($livewire)['description'])
            ->emptyStateIcon(fn ($livewire) => static::emptyState($livewire)['icon'])
            ->emptyStateActions([
                Tables\Actions\Action::make('resetFilters')
                    ->label('Hapus pencarian & saringan')
                    ->icon('heroicon-m-x-mark')
                    ->color('gray')
                    ->action(function ($livewire) {
                        $livewire->resetTableSearch();
                        $livewire->resetTableFiltersForm();
                    })
                    ->visible(fn ($livewire) => static::isNarrowed($livewire)),
                Tables\Actions\Action::make('registerWalkIn')
                    ->label('Daftarkan permohonan loket')
                    ->icon('heroicon-m-plus')
                    ->url(fn () => \App\Filament\Pages\FrontDesk\RegisterService::getUrl())
                    ->visible(fn ($livewire) => ! static::isNarrowed($livewire)
                        && in_array($livewire->activeTab ?? 'semua', ['semua', 'antrian'], true)
                        && \App\Filament\Pages\FrontDesk\RegisterService::canAccess()),
            ]);
    }

    public const DATE_PERIODS = [
        'today' => 'Hari ini',
        'last_7_days' => '7 hari terakhir',
        'this_month' => 'Bulan ini',
        'last_month' => 'Bulan lalu',
        'this_year' => 'Tahun ini',
    ];

    /** @return array{0: ?\Illuminate\Support\Carbon, 1: ?\Illuminate\Support\Carbon} */
    public static function dateRange(array $data): array
    {
        $date = fn (?string $value) => filled($value) ? \Illuminate\Support\Carbon::parse($value)->startOfDay() : null;

        return match ($data['period'] ?? null) {
            'today' => [today(), today()],
            'last_7_days' => [today()->subDays(6), today()],
            'this_month' => [today()->startOfMonth(), today()],
            'last_month' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'this_year' => [today()->startOfYear(), today()],
            default => [$date($data['from'] ?? null), $date($data['until'] ?? null)],
        };
    }

    /** Services grouped by category for the "Jenis layanan" filter. */
    public static function serviceOptions(): array
    {
        $options = [];
        foreach (Service::with('categories:id,name')->orderBy('name')->get(['id', 'name']) as $service) {
            $options[$service->categories->first()?->name ?? 'Lainnya'][$service->id] = $service->name;
        }
        ksort($options);
        if (isset($options['Lainnya'])) {
            $other = $options['Lainnya'];
            unset($options['Lainnya']);
            $options['Lainnya'] = $other;
        }

        return count($options) === 1 ? reset($options) : $options;
    }

    /**
     * Ubah status langsung dari daftar, tanpa membuka tiket dan tanpa muat
     * ulang halaman. The badge switches to the new status at once
     * (optimistic); Livewire then re-renders the row with the saved status,
     * which also undoes the switch if the change is refused. Rejecting and
     * cancelling still ask for a reason.
     *
     * @return array<int, Tables\Actions\Action>
     */
    public static function quickStatusActions(): array
    {
        return collect(TicketService::OFFICER_STATUSES)->map(function (string $label, string $status) {
            $needsReason = in_array($status, ['rejected', 'cancelled'], true);
            $optimistic = 'const badge = $el.closest(\'tr\')?.querySelector(\'[data-ticket-status] .fi-badge\');'
                . ' if (badge) { badge.querySelector(\'.truncate\')?.replaceChildren(' . json_encode($label . ' …') . '); badge.style.opacity = \'.6\'; }';

            return Tables\Actions\Action::make('status_' . $status)
                ->label($label)
                ->icon(match ($status) {
                    'submitted' => 'heroicon-m-arrow-uturn-left',
                    'verified' => 'heroicon-m-check',
                    'in_process' => 'heroicon-m-cog-6-tooth',
                    'completed' => 'heroicon-m-check-circle',
                    'rejected' => 'heroicon-m-x-circle',
                    default => 'heroicon-m-no-symbol',
                })
                ->color(TicketLabels::statusColor($status))
                ->visible(fn (Ticket $record) => array_key_exists($status, app(TicketService::class)->nextStatuses($record, auth()->user())))
                ->extraAttributes($needsReason || $status === 'completed' ? [] : ['x-on:click' => $optimistic])
                ->requiresConfirmation($needsReason || $status === 'completed')
                ->modalHeading(fn (Ticket $record) => "{$label}: {$record->ticket_number}")
                ->modalDescription($status === 'completed' ? 'Pemohon akan diberi tahu bahwa layanan selesai.' : null)
                ->form($needsReason ? [
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label($status === 'rejected' ? 'Alasan penolakan' : 'Alasan pembatalan')
                        ->required()->minLength(10)->maxLength(1000)->rows(3),
                ] : [])
                ->action(function (Ticket $record, array $data, Tables\Actions\Action $action) use ($status, $label) {
                    try {
                        app(TicketService::class)->changeStatus($record, $status, $data['notes'] ?? "Status diubah menjadi {$label} dari daftar permohonan.", auth()->user());
                    } catch (\App\Exceptions\TicketActionException $e) {
                        \Filament\Notifications\Notification::make()->title('Status tidak diubah')->body($e->getMessage())->danger()->send();
                        $action->halt();
                    }

                    \Filament\Notifications\Notification::make()->title("{$record->ticket_number}: {$label}")->success()->send();
                });
        })->values()->all();
    }

    /** Number of filters with a value (each shown as a removable chip above the table). */
    public static function activeFilterCount($livewire): int
    {
        return collect($livewire->tableFilters ?? [])
            ->filter(fn ($filter) => collect((array) $filter)->flatten()->contains(fn ($value) => filled($value) && $value !== false))
            ->count();
    }

    /** "2 filter aktif · kata kunci "siti" · 5 permohonan ditemukan", or null when nothing narrows the list. */
    public static function narrowingSummary($livewire): ?string
    {
        if (! static::isNarrowed($livewire)) {
            return null;
        }

        $parts = [];
        if ($count = static::activeFilterCount($livewire)) {
            $parts[] = "{$count} filter aktif";
        }
        if (filled($search = $livewire->tableSearch ?? null)) {
            $parts[] = 'kata kunci "' . \Illuminate\Support\Str::limit($search, 40) . '"';
        }
        $parts[] = number_format($livewire->getFilteredTableQuery()->count(), 0, ',', '.') . ' permohonan ditemukan';

        return implode(' · ', $parts);
    }

    /** Is the list limited by a search or a filter (rather than simply empty)? */
    public static function isNarrowed($livewire): bool
    {
        return filled($livewire->tableSearch ?? null)
            || collect($livewire->tableFilters ?? [])->flatten()->contains(fn ($value) => filled($value) && $value !== false);
    }

    /**
     * Pesan kosong for the request list: explains why it is empty, per tab,
     * or that the search/filters found nothing.
     *
     * @return array{heading: string, description: string, icon: string}
     */
    public static function emptyState($livewire): array
    {
        if (static::isNarrowed($livewire)) {
            return [
                'heading' => 'Tidak ada permohonan yang cocok',
                'description' => 'Tidak ada permohonan yang sesuai dengan pencarian atau saringan. Ubah kata kunci, longgarkan saringan, atau hapus semuanya.',
                'icon' => 'heroicon-o-magnifying-glass',
            ];
        }

        [$heading, $description, $icon] = match ($livewire->activeTab ?? 'semua') {
            'antrian' => ['Antrian kosong', 'Semua permohonan sudah ditangani. Permohonan baru dari portal atau loket akan muncul di sini.', 'heroicon-o-inbox'],
            'saya' => ['Belum ada tugas untuk Anda', 'Permohonan yang ditugaskan kepada Anda akan muncul di sini.', 'heroicon-o-user'],
            'disposisi-unit' => ['Belum ada disposisi untuk unit Anda', 'Permohonan yang didisposisikan pimpinan ke unit Anda akan muncul di sini.', 'heroicon-o-arrow-right-circle'],
            'terlambat' => ['Tidak ada permohonan terlambat', 'Semua permohonan yang masih berjalan berada dalam target waktu penyelesaian.', 'heroicon-o-check-badge'],
            'persetujuan' => ['Tidak ada yang menunggu persetujuan', 'Permohonan yang memerlukan disposisi atau persetujuan pimpinan akan muncul di sini.', 'heroicon-o-clipboard-document-check'],
            'siap-diambil' => ['Belum ada hasil yang siap diambil', 'Permohonan selesai yang hasilnya bisa diambil di loket akan muncul di sini.', 'heroicon-o-hand-raised'],
            default => ['Belum ada permohonan', 'Permohonan yang diajukan lewat portal online maupun didaftarkan di loket akan tercatat di sini.', 'heroicon-o-ticket'],
        };

        return compact('heading', 'description', 'icon');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Tahapan permohonan')
                ->icon('heroicon-o-map')
                ->schema([
                    ViewEntry::make('progress')
                        ->hiddenLabel()
                        ->view('filament.infolists.ticket-progress'),
                ]),
            Section::make('Ringkasan')
                ->icon('heroicon-o-information-circle')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('incoming_category_summary')->label('Kategori layanan masuk')->badge()
                        ->state(fn (Ticket $record) => $record->approval_required ? null : IncomingCategory::of($record))
                        ->formatStateUsing(fn (string $state) => IncomingCategory::label($state))
                        ->color(fn (string $state) => IncomingCategory::COLORS[$state] ?? 'gray')
                        ->helperText(fn (Ticket $record) => $record->incoming_category ? 'Dipilih petugas' : null)
                        ->visible(fn (Ticket $record) => ! $record->approval_required),
                    TextEntry::make('status')->label('Status')->badge()
                        ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                        ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                    TextEntry::make('service.name')->label('Layanan'),
                    TextEntry::make('mode')->label('Jalur')->formatStateUsing(fn (?string $state) => TicketLabels::mode($state)),
                    TextEntry::make('priority')->label('Prioritas')->badge()
                        ->formatStateUsing(fn (?string $state) => TicketLabels::priority($state))
                        ->color(fn (?string $state) => TicketLabels::priorityColor($state)),
                    TextEntry::make('created_at')->label('Diajukan')->dateTime('d M Y H:i'),
                    TextEntry::make('estimated_completion_date')->label('Target selesai')->date('d M Y')->placeholder('–')
                        ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null),
                    TextEntry::make('actual_completion_date')->label('Selesai')->date('d M Y')->placeholder('Belum selesai'),
                    TextEntry::make('assignedTo.name')->label('Petugas')->placeholder('Belum ditugaskan'),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Pemohon')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Nama'),
                        TextEntry::make('user.user_type')->label('Kategori')->formatStateUsing(fn (?string $state) => \App\Services\FrontDeskService::APPLICANT_TYPES[$state] ?? $state)->placeholder('–'),
                        TextEntry::make('user.email')->label('Email')->copyable()
                            ->formatStateUsing(fn (?string $state) => str_ends_with((string) $state, '@walkin.local') ? '–' : $state),
                        TextEntry::make('user.whatsapp_number')->label('WhatsApp')->placeholder('–')->copyable(),
                    ]),
                Section::make('Persetujuan pimpinan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->visible(fn (Ticket $record) => (bool) $record->approval_required)
                    ->schema([
                        TextEntry::make('approval_status')->label('Keputusan')->badge()
                            ->formatStateUsing(fn (?string $state) => match ($state) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => 'Menunggu',
                            })
                            ->color(fn (?string $state) => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            })
                            ->default('pending'),
                        TextEntry::make('approver.name')->label('Oleh')->placeholder('–'),
                        TextEntry::make('signature_type')->label('Tanda tangan')->formatStateUsing(fn (?string $state) => ServiceDisposition::signatureTypeLabel($state))->placeholder('–'),
                        TextEntry::make('approved_at')->label('Tanggal')->dateTime('d M Y H:i')->placeholder('–'),
                        TextEntry::make('kategori_masuk')->label('Kategori layanan masuk')->badge()
                            ->state(fn (Ticket $record) => $record->approval_status === 'approved' ? IncomingCategory::of($record) : null)
                            ->helperText(fn (Ticket $record) => $record->incoming_category ? 'Dipilih petugas' : null)
                            ->formatStateUsing(fn (string $state) => IncomingCategory::label($state))
                            ->color(fn (string $state) => IncomingCategory::COLORS[$state] ?? 'gray')
                            ->placeholder('–'),
                        TextEntry::make('disposition_recipients')->label('Diteruskan kepada')
                            ->state(fn (Ticket $record) => $record->disposition_recipients ? ServiceDisposition::recipients($record->disposition_recipients) : null)
                            ->placeholder('–')->columnSpanFull(),
                        TextEntry::make('approval_notes')->label('Catatan')->placeholder('–')->columnSpanFull(),
                    ]),
            ]),
            Section::make('Keterangan permohonan')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->schema([
                    TextEntry::make('notes')->hiddenLabel()->placeholder('Tidak ada keterangan.')->prose(),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Berkas')
                    ->icon('heroicon-o-paper-clip')
                    ->description(fn (Ticket $record) => ServiceDisposition::recommendationHint($record->service))
                    ->schema([
                        RepeatableEntry::make('files')
                            ->hiddenLabel()
                            ->contained(false)
                            ->placeholder('Belum ada berkas.')
                            ->schema([
                                TextEntry::make('file_name')
                                    ->hiddenLabel()
                                    ->icon('heroicon-m-document-arrow-down')
                                    ->color('primary')
                                    ->url(fn ($record) => route('documents.ticket-file', $record))
                                    ->openUrlInNewTab()
                                    ->helperText(fn ($record) => 'Diunggah ' . $record->created_at?->format('d M Y H:i')),
                            ]),
                    ]),
                Section::make('Hasil layanan')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        TextEntry::make('output.output_description')
                            ->hiddenLabel()
                            ->default('Unduh hasil layanan')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (Ticket $record) => $record->output ? route('documents.ticket-output', $record->output) : null)
                            ->openUrlInNewTab()
                            ->visible(fn (Ticket $record) => (bool) $record->output),
                        TextEntry::make('ready_for_pickup')
                            ->hiddenLabel()
                            ->state(fn (Ticket $record) => match (true) {
                                $record->status === 'completed' && $record->ready_for_pickup => 'Menunggu diambil pemohon di loket.',
                                $record->status === 'completed' => 'Sudah selesai.',
                                default => 'Belum ada hasil layanan.',
                            })
                            ->visible(fn (Ticket $record) => ! $record->output),
                    ]),
            ]),
            Section::make('Langkah workflow')
                ->icon('heroicon-o-queue-list')
                ->collapsible()
                ->visible(fn (Ticket $record) => $record->workflowSteps->isNotEmpty())
                ->schema([
                    RepeatableEntry::make('workflowSteps')
                        ->hiddenLabel()
                        ->columns(3)
                        ->schema([
                            TextEntry::make('workflowStep.name')->label('Langkah'),
                            TextEntry::make('status')->label('Status')->badge()
                                ->formatStateUsing(fn (?string $state, $record) => $record->completed_at ? 'Selesai' : 'Menunggu')
                                ->color(fn ($record) => $record->completed_at ? 'success' : 'gray'),
                            TextEntry::make('completed_at')->label('Selesai')->dateTime('d M Y H:i')->placeholder('–'),
                        ]),
                ]),
            Section::make('Riwayat status')
                ->icon('heroicon-o-arrow-path')
                ->description('Setiap perubahan status, siapa yang mengubah, dan berapa lama permohonan berada di status itu.')
                ->collapsible()
                ->schema([
                    ViewEntry::make('status_history')
                        ->hiddenLabel()
                        ->view('filament.infolists.status-history'),
                ]),
            Section::make('Riwayat kategori layanan masuk')
                ->icon('heroicon-o-tag')
                ->description('Kategori awal dari disposisi dan setiap perubahan oleh petugas.')
                ->collapsible()
                ->visible(fn (Ticket $record) => ! $record->needsApproval() && $record->approval_status !== 'rejected')
                ->schema([
                    ViewEntry::make('category_history')
                        ->hiddenLabel()
                        ->view('filament.infolists.category-history'),
                ]),
            Section::make('Riwayat disposisi & tanda tangan')
                ->icon('heroicon-o-pencil-square')
                ->collapsible()
                ->visible(fn (Ticket $record) => (bool) $record->approval_required)
                ->schema([
                    ViewEntry::make('disposition_history')
                        ->hiddenLabel()
                        ->view('filament.infolists.disposition-history'),
                ]),
            Section::make('Riwayat berkas & hasil layanan')
                ->icon('heroicon-o-document-duplicate')
                ->collapsible()
                ->collapsed()
                ->schema([
                    ViewEntry::make('document_history')
                        ->hiddenLabel()
                        ->view('filament.infolists.document-history'),
                ]),
            Section::make('Riwayat layanan')
                ->icon('heroicon-o-clock')
                ->description('Tahapan permohonan dari diterima sampai selesai. Riwayat tidak dapat diubah.')
                ->collapsible()
                ->schema([
                    ViewEntry::make('timeline')
                        ->hiddenLabel()
                        ->view('filament.infolists.ticket-timeline'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
