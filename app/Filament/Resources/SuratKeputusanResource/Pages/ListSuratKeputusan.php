<?php

namespace App\Filament\Resources\SuratKeputusanResource\Pages;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use App\Services\SkReportService;
use App\Services\SuratKeputusanService;
use App\Support\RegisterSkAccess;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ListSuratKeputusan extends ListRecords
{
    protected static string $resource = SuratKeputusanResource::class;

    public function getTitle(): string
    {
        return 'Register Penomoran SK';
    }

    public function getSubheading(): ?string
    {
        return 'Buku register Surat Keputusan. Nomor berformat {N} TAHUN {YYYY}, berurutan 1–9999 dan dimulai ulang setiap tahun, terpisah dari nomor surat keluar.';
    }

    /**
     * Minta Nomor SK: the SK data, all required but keterangan; correctable
     * later with "Ubah". "Jumlah nomor" takes several numbers with the same
     * data at once: up to 1000 for an administrator recording numbers already
     * used by hand, up to 10 for other officers (RegisterSkAccess::maxPerRequest).
     * Not offered for a ticket's SK (Registrasi Nomor SK with ?tiket=).
     */
    public static function requestForm(): array
    {
        $max = RegisterSkAccess::maxPerRequest(auth()->user());
        $block = RegisterSkAccess::canRecordBlock(auth()->user());

        return [
            Section::make('Data SK')
                ->description('Lengkapi data SK; setelah nomor terbit, data masih dapat dikoreksi lewat "Ubah".')
                ->compact()
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    SuratKeputusanResource::dateField()
                        ->default(now())
                        ->live()
                        ->helperText('Tahunnya menentukan urutan nomor SK.'),
                    TextInput::make('jumlah')
                        ->label('Jumlah nomor yang dibutuhkan')
                        ->integer()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue($max)
                        ->required()
                        ->live(onBlur: true)
                        ->visible(fn ($livewire) => ! ($livewire instanceof RegisterSkNumber && $livewire->ticket()))
                        ->helperText($block
                            ? "1–{$max} nomor sekaligus (paling banyak " . SuratKeputusanService::MAX_BLOCK . ' per hari), untuk mencatat nomor yang sudah terpakai secara manual. Semua nomor berisi data yang sama; koreksi tiap SK lewat "Ubah".'
                            : "1–{$max} nomor sekaligus, dihitung dalam batas " . SuratKeputusanService::MAX_PER_MINUTE . ' nomor per menit dan ' . SuratKeputusanService::MAX_PER_DAY . ' per hari. Semua nomor berisi data yang sama; koreksi tiap SK lewat "Ubah".')
                        ->validationMessages([
                            'required' => 'Isi jumlah nomor yang dibutuhkan.',
                            'integer' => 'Jumlah nomor berupa bilangan bulat.',
                            'min' => 'Jumlah nomor paling sedikit 1.',
                            'max' => $block
                                ? "Paling banyak {$max} nomor sekaligus."
                                : "Paling banyak {$max} nomor sekaligus; lebih dari itu hanya untuk administrator (pencatatan nomor manual).",
                        ]),
                    ...SuratKeputusanResource::dataFields(),
                ]),
            Section::make('Scan SK (opsional)')
                ->description('Unggah sekarang bila SK sudah ditandatangani, atau nanti lewat "Ubah". Berkas disimpan privat dan hanya dapat dibuka petugas yang berwenang atas register SK.')
                ->compact()
                ->collapsible()
                // One scan belongs to one SK: with several numbers, each scan goes in later through "Ubah".
                ->visible(fn (Get $get) => self::count($get('jumlah')) === 1)
                ->schema([SuratKeputusanResource::scanUpload()]),
            SuratKeputusanResource::officerField(),
            Placeholder::make('preview')
                ->label(fn (Get $get) => self::count($get('jumlah')) > 1 ? 'Nomor yang akan diberikan' . ' (' . self::count($get('jumlah')) . ' nomor)' : 'Nomor yang akan diberikan')
                ->content(fn (Get $get) => self::numberPreview($get('tanggal_surat'), self::count($get('jumlah')))),
            // Numbers are never given back: several at once are confirmed first.
            Checkbox::make('konfirmasi_jumlah')
                ->label(fn (Get $get) => 'Ya, ambil ' . self::count($get('jumlah')) . ' nomor SK sekaligus. Nomor yang sudah diambil tidak dapat dipakai ulang.')
                ->accepted()
                ->dehydrated(false)
                ->visible(fn (Get $get) => self::count($get('jumlah')) > 1)
                ->validationMessages(['accepted' => 'Centang untuk memastikan jumlah nomor yang diambil.']),
        ];
    }

    /** "Jumlah nomor" as typed: blank or not a number counts as one. */
    private static function count(mixed $jumlah): int
    {
        return max(1, (int) (is_numeric($jumlah) ? $jumlah : 1));
    }

    /**
     * Pratinjau: the next number(s) of the year of the chosen date, taken for
     * real only on "Ambil nomor" (another officer may take them first).
     */
    public static function numberPreview(mixed $tanggal, int $count = 1): HtmlString
    {
        $year = rescue(fn () => (int) Carbon::parse($tanggal ?: now())->format('Y'), now()->year, report: false);
        $next = SuratKeputusanService::nextNumber($year);

        if ($next === null) {
            return new HtmlString('<span data-sk-preview="habis" class="text-sm font-medium text-danger-600 dark:text-danger-400">'
                . e('Nomor SK tahun ' . $year . ' sudah mencapai ' . SuratKeputusanService::MAX_NUMBER . '; tidak ada nomor tersisa.') . '</span>');
        }

        $left = SuratKeputusanService::MAX_NUMBER - $next + 1;
        if ($count > $left) {
            return new HtmlString('<span data-sk-preview="kurang" class="text-sm font-medium text-danger-600 dark:text-danger-400">'
                . e("Sisa nomor SK tahun {$year} tinggal {$left}; kurangi jumlahnya.") . '</span>');
        }

        $last = $next + $count - 1;
        $numbers = SuratKeputusan::composeRange($next, $last, $year);
        $note = $count === 1
            ? "Nomor urut {$next} tahun {$year}. Bila petugas lain mengambil nomor lebih dulu, Anda mendapat nomor sesudahnya."
            : "{$count} nomor urut {$next} sampai {$last} tahun {$year}, berurutan tanpa nomor lain di antaranya. Bila petugas lain mengambil nomor lebih dulu, rentangnya bergeser sesudah nomor itu.";

        return new HtmlString('<div data-sk-preview="' . e($numbers) . '" class="space-y-1">'
            . '<p class="font-mono text-2xl font-semibold tracking-wide text-primary-600 dark:text-primary-400">' . e($numbers) . '</p>'
            . '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($note) . '</p>'
            . self::otherYearWarning($year)
            . '</div>');
    }

    /**
     * "Ambil nomor": one SK, or "Jumlah nomor" SK with the same data numbered
     * one after another: an administrator's pencatatan nomor manual
     * (reserveBlock, up to 1000 and its own daily quota), or another
     * officer's kumpulan (reserveMany, up to 10 within the usual limits).
     *
     * @param  array<string, mixed>  $data  the request form's state
     * @return list<SuratKeputusan>
     */
    public static function takeNumbers(array $data, ?int $ticketId = null): array
    {
        $service = app(SuratKeputusanService::class);
        $user = auth()->user();
        $count = self::count($data['jumlah'] ?? 1);
        $date = Carbon::parse($data['tanggal_surat']);
        $data = [...Arr::except($data, ['jumlah', 'konfirmasi_jumlah']), 'ticket_id' => $ticketId];

        if ($count === 1) {
            return [$service->reserve($date, $user, $data)];
        }

        $data = Arr::except($data, ['berkas_scan', 'berkas_scan_nama']);

        return RegisterSkAccess::canRecordBlock($user)
            ? $service->reserveBlock($date, $user, $data, $count)
            : $service->reserveMany($user, array_fill(0, $count, $data));
    }

    /**
     * A date outside this year takes a number of that year's register, a
     * past one included: said plainly before the number is given for good.
     */
    private static function otherYearWarning(int $year): string
    {
        $text = SuratKeputusanService::otherYearNote($year);

        return $text === null ? '' : '<p data-sk-preview-other-year="' . $year . '" class="text-xs font-medium text-warning-600 dark:text-warning-400">' . e($text) . '</p>';
    }

    /**
     * After "Ambil nomor" with several numbers: the range to copy, and what is
     * still to do; one number, as numberGiven.
     *
     * @param  list<SuratKeputusan>  $taken
     */
    public static function numbersGiven(array $taken): Notification
    {
        $first = reset($taken);
        $last = end($taken);
        if ($first->is($last)) {
            return self::numberGiven($first);
        }

        $numbers = SuratKeputusan::composeRange($first->nomor_urut, $last->nomor_urut, $first->tahun);

        return Notification::make()
            ->title(count($taken) . ' nomor SK diberikan')
            ->body(e($numbers) . '<br>' . e('Tentang ' . $first->perihal)
                . '<br><span class="text-xs">Semua nomor berisi data yang sama; koreksi tiap SK dan unggah scannya lewat "Ubah".</span>')
            ->actions([self::copyAction($numbers)])
            ->success()
            ->persistent();
    }

    /** Salin: the number(s) go straight to the clipboard for the SK text. */
    private static function copyAction(string $numbers): \Filament\Notifications\Actions\Action
    {
        return \Filament\Notifications\Actions\Action::make('copy')
            ->label('Salin nomor')
            ->icon('heroicon-m-clipboard-document')
            ->button()
            // A notification's button prints its attributes escaped more than once,
            // so the handler holds no quotes: Alpine could not read 'Tersalin'.
            ->extraAttributes([
                'data-copy-number' => $numbers,
                'x-on:click' => 'window.navigator.clipboard.writeText($el.dataset.copyNumber); $tooltip(`Tersalin`)',
            ]);
    }

    /** After "Ambil nomor": the number to copy into the SK, and what is still to do. */
    public static function numberGiven(SuratKeputusan $sk): Notification
    {
        return Notification::make()
            ->title('Nomor SK diberikan')
            ->body(e($sk->nomor_sk) . '<br>' . e('Tentang ' . $sk->perihal)
                . ($sk->hasScan() ? '' : '<br><span class="text-xs">Scan belum diunggah; unggah lewat "Ubah" setelah SK ditandatangani.</span>'))
            ->actions([self::copyAction($sk->nomor_sk)])
            ->success()
            ->persistent();
    }

    public function table(Table $table): Table
    {
        // Unduh Laporan: the register as listed here is what "Cetak / Unduh" puts in the report.
        return parent::table($table)
            ->heading(fn () => $this->reportPeriod())
            ->description(fn () => $this->reportScope());
    }

    /**
     * The search box looks for the whole phrase in one field, as the API's q
     * does (SuratKeputusan::scopeRegister): "4 TAHUN 2026" is that number, not
     * every SK with a 4 in its keterangan. "Tentang" is left out, as perihal
     * is stored without it.
     */
    protected function extractTableSearchWords(string $search): array
    {
        $phrase = preg_replace('/^tentang\s+/iu', '', Str::squish($search));

        return filled($phrase) ? [$phrase] : [];
    }

    /** The period of the report: the Tahun filter, or every year once it is cleared. */
    public function reportPeriod(): string
    {
        return SkReportService::period($this->getTableFilterState('tahun')['value'] ?? null);
    }

    /** How many SK the downloaded report holds, after filters and search. */
    public function reportScope(): string
    {
        $count = $this->getAllTableRecordsCount();

        return $count === 0
            ? 'Tidak ada SK pada filter dan pencarian ini; laporan yang diunduh akan kosong.'
            : $count . ' SK sesuai filter dan pencarian. "Cetak / Unduh" mengunduh laporan berisi daftar ini.';
    }

    /**
     * Cetak / Unduh: the register as filtered and searched on screen, or a
     * warning instead of an empty file.
     *
     * @param  Closure(Builder, string): mixed  $download  Gets the query and the file name without extension.
     */
    public function downloadReport(string $format, Closure $download): mixed
    {
        $count = $this->getFilteredTableQuery()->count();
        $year = $this->getTableFilterState('tahun')['value'] ?? null;

        if ($count === 0) {
            Notification::make()->title('Tidak ada SK untuk diunduh')->body(SkReportService::emptyMessage($year))->warning()->send();

            return null;
        }

        if ($format === SkReportService::FORMATS['pdf'] && $refusal = SkReportService::pdfRefusal($count)) {
            Notification::make()->title('Laporan PDF terlalu besar')->body($refusal)->warning()->send();

            return null;
        }

        if ($wait = SkReportService::downloadWait(auth()->user())) {
            Notification::make()->title('Terlalu banyak unduhan')->body(SkReportService::downloadWaitMessage($wait))->warning()->send();

            return null;
        }

        SkReportService::log(auth()->user(), $format, $count, $year, ['saringan' => $this->tableFilters, 'pencarian' => $this->tableSearch ?: null, 'sumber' => 'panel']);

        Notification::make()->title('Mengunduh ' . $count . ' SK')->body('Berkas ' . $format . ' ' . Str::lower($this->reportPeriod()) . ' sedang disiapkan oleh peramban.')->success()->send();

        return $download($this->getFilteredSortedTableQuery(), $this->reportFilename());
    }

    /** The table's filters and search in the API's terms (SkReportService::criteria, SuratKeputusan::scopeRegister). */
    public function reportFilters(): array
    {
        $state = fn (string $filter, string $key = 'value') => $this->getTableFilterState($filter)[$key] ?? null;

        return [
            'tahun' => $state('tahun') ?: 'semua',
            'q' => $this->tableSearch ?: null,
            'jenis_sk' => $state('jenis_sk', 'values') ?: null,
            'status' => $state('status'),
            'petugas' => $state('pembuat_id'),
            'dari' => $state('tanggal', 'from'),
            'sampai' => $state('tanggal', 'until'),
        ];
    }

    /** E.g. register-penomoran-sk-2026-20261027-093000, or …-semua-tahun-… once the Tahun filter is cleared. */
    public function reportFilename(): string
    {
        return SkReportService::filename($this->getTableFilterState('tahun')['value'] ?? null);
    }

    /** Like the surat keluar register: the history of numbers taken, and of SK deleted, under the table. */
    protected function getFooterWidgets(): array
    {
        return [\App\Filament\Resources\SuratKeputusanResource\Widgets\SkNumberHistory::class];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\SuratKeputusanResource\Widgets\SkYearOverview::class,
            // Multi-role officers: what the active role may do here, and the switch for the rest.
            \App\Filament\Resources\SuratKeputusanResource\Widgets\RegisterSkRoleAccess::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Cetak / Unduh: the register as filtered and sorted on screen.
            ActionGroup::make([
                Action::make('excel')
                    ->label('Unduh Excel')
                    ->icon('heroicon-o-table-cells')
                    ->tooltip('Semua kolom, urut seperti di layar; untuk diolah lagi di spreadsheet.')
                    ->action(fn () => $this->downloadReport('Excel', fn (Builder $query, string $name) => SkReportService::excel($query, $name))),
                Action::make('pdf')
                    ->label('Cetak / Unduh PDF')
                    ->icon('heroicon-o-printer')
                    ->tooltip('A4 mendatar berkop madrasah, urut nomor SK, paling banyak ' . SkReportService::PDF_MAX_ROWS . ' SK; siap dicetak.')
                    ->action(fn () => $this->downloadReport('PDF', fn (Builder $query, string $name) => SkReportService::pdf($query, $name, SkReportService::criteria($this->reportFilters())))),
            ])
                ->label('Cetak / Unduh')
                ->tooltip('Laporan register sesuai filter dan pencarian; setiap unduhan dicatat di Log & Audit.')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
            Action::make('reserve')
                ->label('Minta Nomor SK')
                ->icon('heroicon-o-hashtag')
                ->visible(fn () => SuratKeputusanResource::canCreate())
                ->modalHeading('Minta nomor SK')
                ->modalDescription('Nomor SK diberikan berurutan per tahun dengan format {N} TAHUN {YYYY}.')
                ->modalSubmitActionLabel('Ambil nomor')
                ->modalWidth('2xl')
                ->form(fn () => self::requestForm())
                // "Ambil & minta lagi": the number is given and the form stays open for the next SK.
                ->extraModalFooterActions(fn (Action $action) => [
                    $action->makeModalSubmitAction('reserveAnother', arguments: ['another' => true])
                        ->label('Ambil & minta lagi'),
                    // The same form as a page of its own (Registrasi Nomor SK).
                    Action::make('openRegistration')->label('Buka sebagai halaman')
                        ->icon('heroicon-m-arrows-pointing-out')
                        ->link()
                        ->url(SuratKeputusanResource::getUrl('registrasi')),
                ])
                ->action(function (array $data, array $arguments, Action $action, Form $form) {
                    try {
                        $taken = self::takeNumbers($data);
                    } catch (TicketActionException $e) {
                        Notification::make()->title('Nomor SK tidak dapat diberikan')->body($e->getMessage())->danger()->send();

                        // Keep the form open with what was typed.
                        $action->halt();
                    }

                    self::numbersGiven($taken)->send();

                    $this->resetTable();
                    // The summary above the table shows the new last and next numbers.
                    $this->dispatch('sk-numbers-changed');

                    if ($arguments['another'] ?? false) {
                        // The next SK usually shares the date and jenis; the rest starts empty.
                        $form->fill(['tanggal_surat' => $data['tanggal_surat'], 'jenis_sk' => $data['jenis_sk'], 'jumlah' => 1]);
                        $action->halt();
                    }
                }),
        ];
    }
}
