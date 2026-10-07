<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Exceptions\TicketActionException;
use App\Exports\SuratKeluarExport;
use App\Filament\Forms\PersuratanFields;
use App\Filament\Resources\SuratKeluarResource;
use App\Services\SuratKeluarService;
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
                        ->live(debounce: 300),
                    DatePicker::make('tanggal_surat')
                        ->label('Tanggal surat')
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->required()
                        ->live(),
                    Section::make('Data surat (opsional)')
                        ->description('Isi sekarang bila sudah diketahui, atau lengkapi nanti per nomor.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            PersuratanFields::tujuan(),
                            TextInput::make('perihal')->label('Perihal')->maxLength(255),
                            PersuratanFields::jenis(),
                            PersuratanFields::klasifikasi()->live(),
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
                                : SuratKeluarNumber::format($first, $date, $get('klasifikasi')) . ($count > 1 ? ' s.d. ' . SuratKeluarNumber::format($last, $date, $get('klasifikasi')) : '');
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
                        ->success()
                        ->persistent()
                        ->send();

                    $this->resetTable();
                }),
        ];
    }
}
