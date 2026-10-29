<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PersuratanFields;
use App\Filament\Resources\SuratKeputusanResource\Pages;
use App\Filament\Resources\SuratKeputusanResource\Widgets;
use App\Models\SuratKeputusan;
use App\Services\SuratKeputusanService;
use App\Support\Impersonation;
use App\Support\Persuratan;
use App\Support\RoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Penomoran SK: the SK register, apart from surat keluar. Numbers read
 * "{N} TAHUN {YYYY}", run 1-9999 and restart every year; access follows
 * SuratKeputusanPolicy (back office, deleting for administrators only).
 */
class SuratKeputusanResource extends Resource
{
    protected static ?string $model = SuratKeputusan::class;

    protected static ?string $slug = 'penomoran-sk';

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Persuratan';

    protected static ?string $navigationLabel = 'Penomoran SK';

    protected static ?string $modelLabel = 'nomor SK';

    protected static ?string $pluralModelLabel = 'Penomoran SK';

    protected static ?string $recordTitleAttribute = 'nomor_sk';

    /** Right below Surat Keluar. */
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('pembuat:id,name');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('nomor')->label('Nomor SK')
                ->content(fn (?SuratKeputusan $record) => $record?->nomor_sk),
            static::dateField()
                ->helperText('Harus dalam tahun nomor SK.')
                ->minDate(fn (?SuratKeputusan $record) => $record ? Carbon::create($record->tahun)->startOfYear() : null)
                ->maxDate(fn (?SuratKeputusan $record) => $record ? Carbon::create($record->tahun)->endOfYear() : null),
            ...static::dataFields(),
            static::scanUpload(),
            static::officerField(),
            // The versi the officer opened; saving checks it is still current (saveCorrection).
            Forms\Components\Hidden::make('versi'),
        ])->columns(1);
    }

    /**
     * "Ubah" (table and Rincian SK): save the correction, or keep the form
     * open with the reason (another officer changed the SK meanwhile, a date
     * outside the SK's year, a jenis no longer offered).
     */
    public static function saveCorrection(SuratKeputusan $record, array $data, Tables\Actions\EditAction|\Filament\Actions\EditAction $action): SuratKeputusan
    {
        try {
            return app(SuratKeputusanService::class)->describe($record, $data);
        } catch (\App\Exceptions\TicketActionException $e) {
            \Filament\Notifications\Notification::make()->title('Koreksi SK tidak disimpan')->body($e->getMessage())->danger()->send();
            $action->halt();

            return $record;
        }
    }

    /** What describes an SK, shared by Minta Nomor SK and Ubah. */
    public static function dataFields(): array
    {
        return [
            PersuratanFields::jenisSk()->required()
                ->live()
                ->helperText(fn (Forms\Get $get, ?SuratKeputusan $record) => static::jenisHelp($get('jenis_sk'), $record?->tahun ?? self::yearOf($get('tanggal_surat'))))
                // Back office may add a missing jenis in Master Persuratan > Jenis SK (opens in a new tab).
                ->hintAction(Forms\Components\Actions\Action::make('kelolaJenisSk')
                    ->label('Kelola jenis SK')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->url(fn () => PersuratanMasterResource::getUrl('index', ['activeTab' => \App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters::JENIS_SK_TAB]), shouldOpenInNewTab: true)
                    ->visible(fn () => PersuratanMasterResource::canViewAny())),
            Forms\Components\TextInput::make('perihal')->label('Perihal')->required()->maxLength(255)
                ->prefix('Tentang')
                ->placeholder('mis. Panitia penerimaan peserta didik baru')
                ->live(onBlur: true)
                ->helperText(fn (Forms\Get $get, ?SuratKeputusan $record) => static::perihalHelp($get('perihal'), $record?->tahun ?? self::yearOf($get('tanggal_surat')), $record))
                // "Tentang" is already the prefix: keep only the subject.
                ->dehydrateStateUsing(fn (?string $state) => preg_replace('/^tentang\s+/iu', '', Str::squish((string) $state)))
                ->validationMessages(['required' => 'Isi perihal SK.', 'max' => 'Perihal paling panjang 255 karakter.'])
                ->columnSpanFull(),
            Forms\Components\TextInput::make('nama_peminta')->label('Nama peminta nomor')->required()->maxLength(255)
                ->placeholder('mis. Waka Kesiswaan')
                ->helperText('Ditulis manual oleh petugas: siapa yang meminta nomor SK ini.')
                ->dehydrateStateUsing(fn (?string $state) => Str::squish((string) $state))
                ->validationMessages(['required' => 'Isi nama peminta nomor.', 'max' => 'Nama peminta paling panjang 255 karakter.'])
                ->columnSpanFull(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->rows(2)->maxLength(1000)
                ->placeholder('Catatan tambahan, mis. masa berlaku atau jumlah lampiran (opsional)')
                ->validationMessages(['max' => 'Keterangan paling panjang 1.000 karakter.'])
                ->columnSpanFull(),
        ];
    }

    /** Unggah Scan SK: one file on the private disk; its original name goes to berkas_scan_nama. */
    public static function scanUpload(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('berkas_scan')->label('Scan SK')
            ->disk('local')
            ->directory(SuratKeputusan::SCAN_DIRECTORY)
            ->visibility('private')
            ->storeFileNamesIn('berkas_scan_nama')
            // Named after the type the file was checked as, not the extension the browser sent.
            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file) => Str::ulid() . '.' . ($file->guessExtension() ?: $file->getClientOriginalExtension()))
            ->acceptedFileTypes(SuratKeputusan::SCAN_MIME_TYPES)
            ->maxSize(SuratKeputusan::SCAN_MAX_SIZE)
            ->downloadable(false)
            ->openable(false)
            ->helperText('PDF atau gambar (JPG/PNG), maks. 10 MB. Boleh menyusul setelah SK ditandatangani; SK berstatus "Terarsip" setelah scannya diunggah.')
            ->validationMessages([
                'max' => 'Ukuran scan paling besar 10 MB.',
                'mimetypes' => 'Scan SK harus berupa PDF, JPG, atau PNG.',
            ])
            ->columnSpanFull();
    }

    /** Under the jenis picker: how often the chosen jenis was used this SK year, or how to pick one. */
    public static function jenisHelp(?string $jenis, int $year): string
    {
        if (blank($jenis)) {
            return Persuratan::options('jenis_sk')
                ? 'Pilih klasifikasi SK; nomor SK tetap satu urutan per tahun untuk semua jenis.'
                : 'Belum ada jenis SK aktif. Tambahkan lewat Master Persuratan > Jenis SK.';
        }

        $usage = SuratKeputusanService::jenisUsage($jenis, $year);

        return $usage['jumlah']
            ? "{$usage['jumlah']} SK jenis ini pada {$year}; terakhir {$usage['terakhir']}."
            : "Belum ada SK jenis ini pada {$year}.";
    }

    /**
     * Under perihal: SK of the same year that read much alike, so a double
     * request is caught before a second number is taken.
     */
    public static function perihalHelp(?string $perihal, int $year, ?SuratKeputusan $record = null): string|HtmlString
    {
        $similar = SuratKeputusanService::similarPerihal($perihal, $year, $record?->getKey());

        if ($similar->isEmpty()) {
            return 'Pokok keputusan seperti tertulis pada judul SK.';
        }

        return new HtmlString('<span data-sk-similar class="text-warning-600 dark:text-warning-400">'
            . e('Mirip SK yang sudah ada: ' . $similar->map(fn (SuratKeputusan $sk) => $sk->nomor_sk . ' (' . $sk->perihal . ')')->join('; ') . '. Pastikan ini bukan permintaan ganda.')
            . '</span>');
    }

    private static function yearOf(mixed $date): int
    {
        return rescue(fn () => (int) Carbon::parse($date ?: now())->format('Y'), now()->year, report: false);
    }

    /** Petugas pencatat: who recorded the SK (and as which role); for a new number, the signed-in officer. */
    public static function officerField(): Forms\Components\Placeholder
    {
        return Forms\Components\Placeholder::make('petugas')->label('Petugas pencatat')
            ->content(function (?SuratKeputusan $record) {
                if ($record) {
                    return SuratKeputusanService::officerLabel($record->pembuat?->name, $record->peran_pembuat);
                }
                $officer = SuratKeputusanService::officer(auth()->user());

                return SuratKeputusanService::officerLabel($officer['nama'], $officer['peran']);
            })
            ->helperText(fn (?SuratKeputusan $record) => match (true) {
                // Ganti akun sementara: the borrowed account is recorded; Log & Audit names the administrator.
                Impersonation::active() => 'Anda sedang memakai akun ini lewat ganti akun sementara. ' . ($record ? 'Perubahan' : 'Nomor') . ' dicatat atas nama akun ini; Log & Audit mencatat Anda sebagai pelaku sebenarnya.',
                $record !== null => null,
                default => 'Terisi otomatis dari akun dan peran aktif yang sedang masuk; tidak dapat diganti.',
            })
            ->hint(fn () => ($session = Impersonation::current()) ? 'Ganti akun oleh ' . $session['admin_name'] : null)
            ->hintIcon(fn () => Impersonation::active() ? 'heroicon-m-exclamation-triangle' : null)
            ->hintColor('warning');
    }

    /** Tanggal surat: its year decides which yearly sequence the number comes from. */
    public static function dateField(): Forms\Components\DatePicker
    {
        return Forms\Components\DatePicker::make('tanggal_surat')->label('Tanggal surat')
            ->native(false)->displayFormat('d M Y')->required()
            ->minDate('2000-01-01')->maxDate('2100-12-31')
            ->validationMessages(['required' => 'Pilih tanggal surat; tahunnya menentukan nomor SK.']);
    }

    /**
     * Hapus (admin only, via SuratKeputusanPolicy): asks for a reason, which
     * goes to Log & Audit with the SK's data. Shared by the table and Rincian SK.
     *
     * @template T of Tables\Actions\DeleteAction|\Filament\Actions\DeleteAction
     *
     * @param  T  $action
     * @return T
     */
    public static function deleteAction(Tables\Actions\DeleteAction|\Filament\Actions\DeleteAction $action): Tables\Actions\DeleteAction|\Filament\Actions\DeleteAction
    {
        return $action
            ->label('Hapus')
            ->modalHeading(fn (SuratKeputusan $record) => 'Hapus SK ' . $record->nomor_sk . '?')
            ->modalDescription('Data SK ini dihapus dari register; nomornya tidak akan diberikan lagi. Alasan penghapusan dicatat di Log & Audit.')
            ->form([
                Forms\Components\Textarea::make('alasan')->label('Alasan penghapusan')
                    ->required()->minLength(5)->maxLength(500)->rows(3)
                    ->validationMessages(['required' => 'Tulis alasan penghapusan; dicatat di Log & Audit.']),
            ])
            // Filament treats a falsy result as a failed delete: no notification, no redirect off Rincian SK.
            ->using(function (SuratKeputusan $record, array $data) {
                app(SuratKeputusanService::class)->delete($record, auth()->user(), $data['alasan']);

                return true;
            })
            ->successNotificationTitle('SK dihapus');
    }

    /** Rincian SK: the SK as recorded, its scan archive, and who recorded and last changed it. */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Surat Keputusan')
                ->icon('heroicon-o-document-check')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('nomor_sk')->label('Nomor SK')->copyable()->copyMessage('Nomor SK tersalin')->weight('semibold'),
                    TextEntry::make('nomor_urut')->label('No. urut')->fontFamily('mono'),
                    TextEntry::make('tahun')->label('Tahun register'),
                    static::statusEntry(),
                    TextEntry::make('tanggal_surat')->label('Tanggal surat')->date('d M Y'),
                    TextEntry::make('jenis_sk')->label('Jenis SK')->badge()->color('gray')->columnSpan(['default' => 1, 'lg' => 3]),
                    TextEntry::make('perihal')->label('Perihal')->prefix('Tentang ')->weight('semibold')->columnSpanFull(),
                    TextEntry::make('nama_peminta')->label('Nama peminta nomor')->columnSpan(['default' => 1, 'sm' => 2]),
                    TextEntry::make('keterangan')->label('Keterangan')->placeholder('–')->columnSpan(['default' => 1, 'sm' => 2]),
                    // Permohonan terkait: a link to the ticket for whoever may see it.
                    TextEntry::make('tiket')->label('Permohonan terkait')
                        ->state(fn (SuratKeputusan $record) => $record->ticket ? $record->ticket->ticket_number . ($record->ticket->trashed() ? ' (dihapus)' : '') : null)
                        ->url(fn (SuratKeputusan $record) => $record->ticket && ! $record->ticket->trashed() && auth()->user()?->can('view', $record->ticket)
                            ? TicketResource::getUrl('view', ['record' => $record->ticket])
                            : null)
                        ->color('primary')
                        ->visible(fn (SuratKeputusan $record) => $record->ticket_id !== null)
                        ->columnSpan(['default' => 1, 'sm' => 2]),
                ]),
            Section::make('Arsip scan SK')
                ->icon('heroicon-o-archive-box')
                ->description('Salinan SK yang sudah ditandatangani, disimpan di penyimpanan privat.')
                ->columns(['default' => 1, 'sm' => 4])
                ->schema([
                    TextEntry::make('scan')->label('Berkas scan')
                        ->state(fn (SuratKeputusan $record) => $record->scanName())
                        ->icon(fn (SuratKeputusan $record) => $record->hasScan() ? 'heroicon-m-document' : null)
                        ->placeholder('Belum ada scan diunggah. Unggah lewat "Ubah" setelah SK ditandatangani.')
                        ->columnSpan(['default' => 1, 'sm' => 3]),
                    TextEntry::make('jenis_berkas')->label('Jenis berkas')
                        ->state(fn (SuratKeputusan $record) => $record->scanType())
                        ->badge()->color('gray')
                        ->placeholder('–'),
                    TextEntry::make('berkas_scan_ukuran')->label('Ukuran')
                        ->formatStateUsing(fn (int $state) => Number::withLocale(app()->getLocale(), fn () => Number::fileSize($state, maxPrecision: 1)))
                        ->visible(fn (SuratKeputusan $record) => $record->hasScan())
                        ->placeholder('–'),
                    TextEntry::make('scan_diunggah_pada')->label('Diunggah')
                        ->dateTime('d M Y H:i')->sinceTooltip()
                        ->helperText(fn (SuratKeputusan $record) => $record->scan_diunggah_oleh ? 'oleh ' . ($record->pengunggahScan?->name ?? 'Akun sudah dihapus') : null)
                        ->visible(fn (SuratKeputusan $record) => $record->hasScan())
                        ->columnSpan(['default' => 1, 'sm' => 3]),
                    Actions::make([
                        InfolistAction::make('lihatScan')->label('Lihat scan')
                            ->icon('heroicon-m-eye')
                            ->url(fn (SuratKeputusan $record) => static::scanUrl($record), shouldOpenInNewTab: true),
                        InfolistAction::make('unduhScan')->label('Unduh scan')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->color('gray')
                            ->url(fn (SuratKeputusan $record) => static::scanUrl($record, download: true)),
                    ])
                        ->visible(fn (SuratKeputusan $record) => $record->hasScan())
                        ->columnSpanFull(),
                ]),
            // Riwayat perubahan: the SK's own trail in Log & Audit, newest first.
            Section::make('Riwayat perubahan')
                ->icon('heroicon-o-clock')
                ->collapsible()
                ->collapsed()
                ->schema([
                    \Filament\Infolists\Components\ViewEntry::make('riwayat')->hiddenLabel()
                        ->state(fn (SuratKeputusan $record) => array_reverse(SuratKeputusanService::history($record)))
                        ->view('filament.resources.surat-keputusan-resource.history'),
                ]),
            Section::make('Pencatatan')
                ->icon('heroicon-o-user')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('petugas')->label('Petugas pencatat')
                        ->state(fn (SuratKeputusan $record) => $record->pembuat?->name)
                        ->placeholder('Akun sudah dihapus')
                        ->helperText(fn (SuratKeputusan $record) => ($admin = SuratKeputusanService::takenByImpersonator($record))
                            ? 'Diambil oleh ' . $admin . ' saat memakai akun ini (ganti akun sementara)'
                            : null),
                    TextEntry::make('peran_pembuat')->label('Sebagai')
                        ->formatStateUsing(fn (string $state) => RoleAccess::roleLabel($state))
                        ->badge()->color('gray')
                        ->placeholder('–'),
                    TextEntry::make('created_at')->label('Dicatat')->dateTime('d M Y H:i')->sinceTooltip(),
                    TextEntry::make('updated_at')->label('Terakhir diubah')
                        // Untouched since the number was taken: nothing to show yet.
                        ->state(fn (SuratKeputusan $record) => $record->updated_at?->gt($record->created_at) ? $record->updated_at : null)
                        ->dateTime('d M Y H:i')->sinceTooltip()
                        ->placeholder('Belum pernah diubah')
                        ->helperText(function (SuratKeputusan $record) {
                            if (! $by = SuratKeputusanService::lastEditor($record)) {
                                return null;
                            }
                            $admin = SuratKeputusanService::lastChange($record)['ganti_akun_oleh'];

                            return 'oleh ' . $by . ($admin ? ', lewat ganti akun oleh ' . $admin : '');
                        }),
                ]),
        ]);
    }

    /** Arsip scan SK through the signed-in panel only: shown in the browser, or downloaded under its own name. */
    public static function scanUrl(SuratKeputusan $record, bool $download = false): string
    {
        return route('surat-keputusan.scan', [$record, ...($download ? ['unduh' => 1] : [])]);
    }

    /** Terarsip once the scan is uploaded, otherwise still waiting for it. */
    public static function statusEntry(): TextEntry
    {
        return TextEntry::make('status')->label('Status')->badge()
            ->state(fn (SuratKeputusan $record) => $record->status())
            ->formatStateUsing(fn (string $state) => SuratKeputusan::STATUS[$state] ?? $state)
            ->color(fn (string $state) => $state === 'terarsip' ? 'success' : 'warning')
            ->icon(fn (string $state) => $state === 'terarsip' ? 'heroicon-m-document-check' : 'heroicon-m-arrow-up-tray');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Nomor urut tahunan, left of the full number: the quickest way to spot gaps or find an SK.
                Tables\Columns\TextColumn::make('nomor_urut')->label('No. Urut')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction))
                    ->searchable(query: fn (Builder $query, string $search) => ctype_digit(trim($search))
                        ? $query->orWhere('nomor_urut', (int) trim($search))
                        : $query)
                    ->alignCenter()
                    ->fontFamily('mono')
                    ->width('1%'),
                Tables\Columns\TextColumn::make('nomor_sk')->label('Nomor SK')->searchable()->copyable()
                    ->copyMessage('Nomor SK tersalin')
                    ->weight('semibold')
                    ->extraAttributes(['class' => 'whitespace-nowrap']),
                Tables\Columns\TextColumn::make('tanggal_surat')->label('Tanggal')->date('d M Y')->sortable()
                    ->extraAttributes(['class' => 'whitespace-nowrap']),
                // Keterangan and the ticket an SK was asked for from are searched too (as the API's q).
                Tables\Columns\TextColumn::make('perihal')->label('Perihal')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $q) => $q
                        ->where('perihal', 'ilike', '%' . addcslashes($search, '%_\\') . '%')
                        ->orWhere('keterangan', 'ilike', '%' . addcslashes($search, '%_\\') . '%')
                        ->orWhereHas('ticket', fn (Builder $t) => $t->where('ticket_number', 'ilike', '%' . addcslashes($search, '%_\\') . '%'))))
                    ->wrap()->limit(80)
                    ->description(fn (SuratKeputusan $record) => $record->keterangan ? Str::limit($record->keterangan, 60) : null),
                Tables\Columns\TextColumn::make('jenis_sk')->label('Jenis SK')->badge()->color('gray')->wrap()->searchable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('nama_peminta')->label('Peminta')->searchable()->wrap()->visibleFrom('md'),
                Tables\Columns\TextColumn::make('pembuat.name')->label('Petugas')->placeholder('–')->toggleable()->visibleFrom('xl')
                    ->description(fn (SuratKeputusan $record) => $record->peran_pembuat ? RoleAccess::roleLabel($record->peran_pembuat) : null),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->state(fn (SuratKeputusan $record) => $record->status())
                    ->formatStateUsing(fn (string $state) => SuratKeputusan::STATUS[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'terarsip' ? 'success' : 'warning')
                    ->icon(fn (string $state) => $state === 'terarsip' ? 'heroicon-m-document-check' : 'heroicon-m-arrow-up-tray')
                    ->tooltip(fn (SuratKeputusan $record) => $record->hasScan() ? 'Scan SK sudah diunggah' : 'Nomor sudah terbit; scan SK belum diunggah'),
            ])
            // Newest first: this year's latest number on top.
            ->defaultSort(fn (Builder $query) => $query->orderByDesc('tahun')->orderByDesc('nomor_urut'))
            ->searchPlaceholder('Cari no. urut, nomor SK, perihal, keterangan/no. tiket, jenis, peminta')
            // Back from Minta Nomor SK or another page: the officer finds the register as they left it.
            ->persistSearchInSession()
            ->persistFiltersInSession()
            // Saringan above the register, folded away until needed: they are also the criteria of the report.
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 4])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')->label('Tahun')
                    ->options(fn () => static::yearOptions())
                    ->default(now()->year),
                Tables\Filters\SelectFilter::make('jenis_sk')->label('Jenis SK')
                    ->options(fn () => static::jenisOptions())
                    ->multiple()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')->label('Status')
                    ->options(SuratKeputusan::STATUS)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) ? $query->withStatus($data['value']) : $query),
                Tables\Filters\SelectFilter::make('pembuat_id')->label('Petugas')
                    ->relationship('pembuat', 'name')->searchable()->preload(),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        // A range that ends before it starts cannot be picked.
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal')->native(false)->displayFormat('d M Y')
                            ->maxDate(fn (Forms\Get $get) => $get('until')),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal')->native(false)->displayFormat('d M Y')
                            ->minDate(fn (Forms\Get $get) => $get('from')),
                    ])
                    ->columns(2)
                    ->columnSpan(['default' => 'full', 'xl' => 2])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_surat', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal_surat', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? 'Dari ' . Carbon::parse($data['from'])->translatedFormat('j M Y') : null,
                        ($data['until'] ?? null) ? 'Sampai ' . Carbon::parse($data['until'])->translatedFormat('j M Y') : null,
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\Action::make('scan')->label('Scan')
                    ->icon('heroicon-m-paper-clip')
                    ->color('gray')
                    ->tooltip(fn (SuratKeputusan $record) => $record->scanName())
                    ->visible(fn (SuratKeputusan $record) => $record->hasScan())
                    ->url(fn (SuratKeputusan $record) => static::scanUrl($record), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->modalHeading(fn (SuratKeputusan $record) => 'Data SK ' . $record->nomor_sk)
                    ->using(fn (SuratKeputusan $record, array $data, Tables\Actions\EditAction $action) => static::saveCorrection($record, $data, $action)),
                static::deleteAction(Tables\Actions\DeleteAction::make()),
            ])
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
                        // Back to the defaults: this year's register.
                        $livewire->resetTableFiltersForm();
                    })
                    ->visible(fn ($livewire) => static::isNarrowed($livewire)),
            ]);
    }

    /** Search or saringan beyond the default (this year's register) are in use. */
    public static function isNarrowed($livewire): bool
    {
        $filters = $livewire->tableFilters ?? [];
        $year = $filters['tahun']['value'] ?? null;

        return filled($livewire->tableSearch ?? null)
            || (filled($year) && (int) $year !== now()->year)
            || collect(Arr::except($filters, 'tahun'))->flatten()->contains(fn ($value) => filled($value) && $value !== false);
    }

    /**
     * Pesan kosong for the register: nothing matches the search or saringan,
     * or no number has been given yet.
     *
     * @return array{heading: string, description: string, icon: string}
     */
    public static function emptyState($livewire): array
    {
        if (static::isNarrowed($livewire)) {
            return [
                'heading' => 'Tidak ada SK yang cocok',
                'description' => 'Tidak ada SK yang sesuai dengan pencarian atau saringan. Ubah kata kunci, longgarkan saringan, atau hapus semuanya.',
                'icon' => 'heroicon-o-magnifying-glass',
            ];
        }

        $year = $livewire->tableFilters['tahun']['value'] ?? null;

        return [
            'heading' => filled($year) ? 'Belum ada nomor SK tahun ' . $year : 'Belum ada nomor SK',
            'description' => 'Nomor SK yang diminta tercatat di sini, berurutan per tahun dan terpisah dari nomor surat keluar.',
            'icon' => 'heroicon-o-document-check',
        ];
    }

    /** Years that have SK, newest first; this year is always offered. */
    public static function yearOptions(): array
    {
        return collect(SuratKeputusanService::years())->mapWithKeys(fn (int $year) => [$year => (string) $year])->all();
    }

    /** Jenis SK in Master Persuratan order, then jenis only found on old SK. */
    public static function jenisOptions(): array
    {
        return collect(SuratKeputusanService::jenisOptions())->mapWithKeys(fn (string $name) => [$name => $name])->all();
    }

    public static function getWidgets(): array
    {
        return [Widgets\SkYearOverview::class, Widgets\RegisterSkRoleAccess::class, Widgets\SkNumberHistory::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuratKeputusan::route('/'),
            // Before '/{record}', which would take "registrasi" for an SK.
            'registrasi' => Pages\RegisterSkNumber::route('/registrasi'),
            'view' => Pages\ViewSuratKeputusan::route('/{record}'),
        ];
    }
}
