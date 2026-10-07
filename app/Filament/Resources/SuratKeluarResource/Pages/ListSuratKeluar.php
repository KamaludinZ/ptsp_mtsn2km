<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Exceptions\TicketActionException;
use App\Exports\SuratKeluarExport;
use App\Filament\Forms\PersuratanFields;
use App\Filament\Resources\SuratKeluarResource;
use App\Services\SuratKeluarService;
use App\Support\NomorFormatSettings;
use App\Support\SuratKeluarNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ListSuratKeluar extends ListRecords
{
    protected static string $resource = SuratKeluarResource::class;

    public function getSubheading(): ?string
    {
        return 'Buku register surat keluar. Nomor berurutan 1–9999 dan dimulai ulang setiap tahun.';
    }

    protected function getFooterWidgets(): array
    {
        return [\App\Filament\Resources\SuratKeluarResource\Widgets\NumberRequestHistory::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('excel')
                    ->label('Unduh Excel')
                    ->icon('heroicon-o-table-cells')
                    ->action(fn () => Excel::download(
                        new SuratKeluarExport($this->getFilteredSortedTableQuery()),
                        'register-surat-keluar-' . now()->format('Ymd-His') . '.xlsx',
                    )),
                Action::make('pdf')
                    ->label('Cetak / Unduh PDF')
                    ->icon('heroicon-o-printer')
                    ->action(function () {
                        $letters = $this->getFilteredSortedTableQuery()->with('pembuat:id,name')->reorder('tahun')->orderBy('nomor_urut')->get();
                        $pdf = Pdf::loadView('pdf.surat-keluar-register', [
                            'letters' => $letters,
                            'years' => $letters->pluck('tahun')->unique()->sort()->join(', '),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(fn () => print $pdf->output(), 'register-surat-keluar-' . now()->format('Ymd-His') . '.pdf');
                    }),
            ])
                ->label('Cetak / Unduh')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
            Action::make('reserve')
                ->label('Minta Nomor Surat')
                ->icon('heroicon-o-hashtag')
                ->modalHeading('Minta nomor surat keluar')
                ->modalDescription('Nomor diberikan berurutan. Data surat dapat dilengkapi setelah nomor didapat.')
                ->modalSubmitActionLabel('Ambil nomor')
                ->form([
                    TextInput::make('count')
                        ->label('Jumlah nomor yang dibutuhkan')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(SuratKeluarService::MAX_PER_REQUEST)
                        ->default(1)
                        ->required()
                        ->live(debounce: 300)
                        ->validationMessages([
                            'required' => 'Isi jumlah nomor yang dibutuhkan.',
                            'min' => 'Minta paling sedikit 1 nomor.',
                            'max' => 'Paling banyak ' . SuratKeluarService::MAX_PER_REQUEST . ' nomor sekali minta.',
                            'integer' => 'Jumlah nomor harus bilangan bulat.',
                        ]),
                    DatePicker::make('tanggal_surat')
                        ->label('Tanggal surat')
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->required()
                        ->live()
                        ->validationMessages(['required' => 'Pilih tanggal surat; bulan dan tahunnya masuk ke nomor.']),
                    // What shapes the number: the jenis surat (format and variable) and the classification.
                    Section::make('Penomoran')
                        ->description('Jenis surat menentukan format nomor dan isian tambahan yang diminta.')
                        ->compact()
                        ->schema([
                            PersuratanFields::jenis()->live(debounce: 400)
                                ->helperText(fn (Get $get) => ($jenis = SuratKeluarService::jenisSurat($get('jenis_surat')))
                                    ? 'Format: ' . NomorFormatSettings::effectiveFormat($jenis)
                                    : 'Jenis di luar daftar memakai format bawaan.')
                                // Another jenis surat asks for another variable: start its field empty.
                                ->afterStateUpdated(fn (\Filament\Forms\Set $set, ?string $state, ?string $old) => SuratKeluarService::jenisSurat($state)?->is(SuratKeluarService::jenisSurat($old)) ? null : $set('variabel', null)),
                            Placeholder::make('ringkasan_jenis')
                                ->label('Ringkasan jenis surat')
                                ->content(fn (Get $get) => self::letterTypeSummary($get('jenis_surat')))
                                ->visible(fn (Get $get) => filled($get('jenis_surat'))),
                            self::variableField(),
                            PersuratanFields::klasifikasi()->live(),
                        ]),
                    Section::make('Data surat (opsional)')
                        ->description('Isi sekarang bila sudah diketahui, atau lengkapi nanti per nomor.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            PersuratanFields::tujuan(),
                            TextInput::make('perihal')->label('Perihal')->maxLength(255),
                            Textarea::make('lampiran')->label('Lampiran')->rows(2)->maxLength(1000),
                            SuratKeluarResource::attachmentUpload()
                                ->visible(fn (Get $get) => (int) $get('count') === 1),
                            PersuratanFields::tembusan(),
                            Textarea::make('keterangan')->label('Keterangan')->rows(2)->maxLength(1000),
                        ]),
                    Placeholder::make('preview')
                        ->label('Nomor yang akan diberikan')
                        ->content(function (Get $get) {
                            $count = max(1, min((int) $get('count'), SuratKeluarService::MAX_PER_REQUEST));
                            $date = Carbon::parse($get('tanggal_surat') ?: now());
                            $first = app(SuratKeluarService::class)->nextNumber((int) $date->format('Y'));
                            $last = $first + $count - 1;

                            return $last > SuratKeluarNumber::MAX
                                ? 'Nomor tahun ' . $date->format('Y') . ' tidak mencukupi.'
                                : SuratKeluarService::composeNumber($first, $date, $get('klasifikasi'), $get('jenis_surat'), $get('variabel')) . ($count > 1 ? ' s.d. ' . SuratKeluarService::composeNumber($last, $date, $get('klasifikasi'), $get('jenis_surat'), $get('variabel')) : '');
                        }),
                ])
                ->action(function (array $data) {
                    try {
                        $letters = app(SuratKeluarService::class)->reserve((int) $data['count'], Carbon::parse($data['tanggal_surat']), auth()->user(), array_filter($data, 'filled'));
                    } catch (TicketActionException $e) {
                        Notification::make()->title('Nomor tidak dapat diberikan')->body($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()
                        ->title($letters->count() . ' nomor surat diberikan')
                        ->body($letters->pluck('nomor_surat')->join('<br>'))
                        // Salin: the numbers go straight to the clipboard for the letter text.
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('copy')
                                ->label($letters->count() === 1 ? 'Salin nomor' : 'Salin semua nomor')
                                ->icon('heroicon-m-clipboard-document')
                                ->button()
                                ->extraAttributes([
                                    'data-copy-number' => $letters->pluck('nomor_surat')->join("\n"),
                                    'x-on:click' => 'window.navigator.clipboard.writeText($el.dataset.copyNumber); $tooltip(' . \Illuminate\Support\Js::from('Tersalin') . ')',
                                ]),
                        ])
                        ->success()
                        ->persistent()
                        ->send();

                    $this->resetTable();
                }),
        ];
    }

    /** One-line summary of the chosen jenis surat: format, month mode, {S}, and its variable. */
    public static function letterTypeSummary(?string $jenisSurat): \Illuminate\Support\HtmlString
    {
        $jenis = SuratKeluarService::jenisSurat($jenisSurat);
        if (! $jenis) {
            return new \Illuminate\Support\HtmlString('<span data-letter-type-summary="unknown" class="text-sm text-gray-500 dark:text-gray-400">Tidak ada di Master Persuratan: nomor memakai format bawaan ' . e(\App\Support\NomorFormat::DEFAULT) . '.</span>');
        }

        $settings = NomorFormatSettings::for($jenis);
        $variable = NomorFormatSettings::variable($jenis);
        $items = [
            'Format' => NomorFormatSettings::effectiveFormat($jenis),
            'Bulan' => \App\Models\PersuratanMaster::MODE_BULAN[$settings['mode_bulan']] ?? $settings['mode_bulan'],
            'Singkatan' => NomorFormatSettings::effectiveSingkatan($jenis) . ($settings['singkatan'] ? ' (khusus)' : ''),
            'Variabel' => $variable ? $variable['label'] . ($variable['wajib'] ? ' (wajib)' : ' (opsional)') : 'tidak ada',
        ];

        return new \Illuminate\Support\HtmlString('<dl data-letter-type-summary="known" class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-0.5 text-sm">'
            . collect($items)->map(fn (string $value, string $label) => '<dt class="text-gray-500 dark:text-gray-400">' . e($label) . '</dt><dd class="font-mono text-gray-950 dark:text-white">' . e($value) . '</dd>')->join('')
            . '</dl>');
    }

    /** Isian variabel tambahan ({v}/{V}): appears only when the chosen jenis surat defines one. */
    public static function variableField(): TextInput
    {
        $variable = fn (Get $get) => NomorFormatSettings::variable(SuratKeluarService::jenisSurat($get('jenis_surat')));

        return TextInput::make('variabel')
            ->label(fn (Get $get) => $variable($get)['label'] ?? 'Variabel tambahan')
            ->helperText(fn (Get $get) => $variable($get)['keterangan'] ?? null)
            ->maxLength(50)
            ->live(debounce: 400)
            ->visible(fn (Get $get) => (bool) $variable($get))
            ->required(fn (Get $get) => $variable($get)['wajib'] ?? false)
            // A slash would add a part to the number.
            ->notRegex('#/#')
            ->validationMessages([
                'required' => fn (Get $get) => ($variable($get)['label'] ?? 'Variabel tambahan') . ' wajib diisi untuk jenis surat ini.',
                'not_regex' => 'Tidak boleh berisi garis miring (/).',
                'max' => 'Paling panjang 50 karakter.',
            ]);
    }
}
