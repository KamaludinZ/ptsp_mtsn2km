<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Resources\SuratKeputusanResource;
use App\Services\SkReportService;
use App\Services\SuratKeputusanService;
use App\Support\SkRegisterRecap as Recap;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Rekap Register SK (/cp/laporan/register-sk): one year of the SK register
 * for evaluation, for whoever works the register (leaders included):
 * numbers given, scan archive completeness and speed, SK deleted, and the
 * year per month, jenis SK and petugas pencatat.
 */
class SkRegisterRecap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $title = 'Rekap Register SK';

    protected static ?string $navigationLabel = 'Rekap Register SK';

    protected static ?string $slug = 'laporan/register-sk';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.sk-register-recap';

    #[Url(as: 'tahun')]
    public ?int $year = null;

    public static function canAccess(): bool
    {
        return SuratKeputusanResource::canViewAny();
    }

    public static function getNavigationGroup(): ?string
    {
        return Performance::getNavigationGroup();
    }

    public function mount(): void
    {
        $this->year = in_array($this->year, $this->years(), true) ? $this->year : now()->year;
    }

    public function updatedYear(): void
    {
        $this->mount();
        unset($this->recap);
    }

    public function getSubheading(): ?string
    {
        return 'Nomor SK yang terbit per tahun, kelengkapan arsip scan, dan sebaran per bulan, jenis SK, serta petugas pencatat. Bulan mengikuti tanggal surat.';
    }

    /** @return list<int> Years with SK, newest first; this year always. */
    public function years(): array
    {
        return SuratKeputusanService::years();
    }

    #[Computed]
    public function recap(): array
    {
        return Recap::build($this->year ?? now()->year);
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('excel')->label('Unduh Excel')->icon('heroicon-o-table-cells')
                    ->action(fn () => $this->download('excel')),
                Action::make('pdf')->label('Cetak / Unduh PDF')->icon('heroicon-o-printer')
                    ->action(fn () => $this->download('pdf')),
            ])
                ->label('Unduh rekap')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button()
                ->tooltip('Rekap tahun yang dipilih; setiap unduhan dicatat di Log & Audit.'),
        ];
    }

    /** The recap leaves the panel: logged in Log & Audit (Register SK), within the download limit. */
    private function download(string $format): mixed
    {
        if ($wait = SkReportService::downloadWait(auth()->user())) {
            Notification::make()->title('Terlalu banyak unduhan')->body(SkReportService::downloadWaitMessage($wait))->warning()->send();

            return null;
        }

        SkReportService::logRecap(auth()->user(), SkReportService::FORMATS[$format], $this->recap);

        return SkReportService::recapFile($this->recap, $format);
    }

    /** The register of that year, to see the SK behind the numbers. */
    public function registerUrl(array $filters = []): string
    {
        return SuratKeputusanResource::getUrl('index', ['tableFilters' => ['tahun' => ['value' => $this->year], ...$filters]]);
    }

    /** Rincian per bulan: the register of one month (by tanggal surat). */
    public function monthUrl(int $month): string
    {
        $start = Carbon::create($this->year, $month)->startOfMonth();

        return $this->registerUrl(['tanggal' => ['from' => $start->toDateString(), 'until' => $start->endOfMonth()->toDateString()]]);
    }
}
