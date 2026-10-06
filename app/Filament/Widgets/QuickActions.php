<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FrontDesk\RegisterService;
use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Pages\Reports\Performance;
use App\Filament\Pages\Reports\SurveyReport;
use App\Filament\Pages\System\Security;
use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VisitorResource;
use Filament\Widgets\Widget;

/**
 * "Akses Cepat": shortcuts to the menus a role uses most. Each shortcut is
 * only listed when the signed-in user may open its page.
 */
class QuickActions extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.quick-actions';

    public static function canView(): bool
    {
        return auth()->check();
    }

    /** @return array<int, array{label: string, description: string, icon: string, url: string}> */
    public function getActions(): array
    {
        $candidates = [
            [RegisterService::canAccess(), 'Registrasi Layanan', 'Daftarkan permohonan di loket', 'heroicon-o-document-plus', fn () => RegisterService::getUrl()],
            [VisitorResource::canCreate(), 'Catat Tamu', 'Isi buku tamu pengunjung', 'heroicon-o-user-plus', fn () => VisitorResource::getUrl('create')],
            [Approvals::canAccess() && Approvals::shouldRegisterNavigation(), 'Disposisi', 'Permohonan menunggu keputusan', 'heroicon-o-clipboard-document-check', fn () => Approvals::getUrl()],
            [TicketResource::canViewAny(), 'Permohonan', 'Daftar & proses permohonan', 'heroicon-o-ticket', fn () => TicketResource::getUrl('index')],
            [ComplaintResource::canViewAny(), 'Pengaduan', 'Tindak lanjut pengaduan & WBS', 'heroicon-o-megaphone', fn () => ComplaintResource::getUrl('index')],
            [Performance::canAccess(), 'Kinerja Pelayanan', 'Laporan & rekap layanan', 'heroicon-o-chart-pie', fn () => Performance::getUrl()],
            [SurveyReport::canAccess(), 'Laporan SKM & SPAK', 'Indeks kepuasan masyarakat', 'heroicon-o-presentation-chart-line', fn () => SurveyReport::getUrl()],
            [ServiceResource::canViewAny(), 'Katalog Layanan', 'Kelola jenis layanan', 'heroicon-o-rectangle-stack', fn () => ServiceResource::getUrl('index')],
            [UserResource::canViewAny(), 'Pengguna', 'Kelola akun & peran', 'heroicon-o-users', fn () => UserResource::getUrl('index')],
            [Security::canAccess(), 'Keamanan Sistem', 'Pantau login & aktivitas', 'heroicon-o-shield-check', fn () => Security::getUrl()],
        ];

        return collect($candidates)
            ->filter(fn (array $item) => $item[0])
            ->map(fn (array $item) => [
                'label' => $item[1],
                'description' => $item[2],
                'icon' => $item[3],
                'url' => ($item[4])(),
            ])
            ->values()
            ->all();
    }
}
