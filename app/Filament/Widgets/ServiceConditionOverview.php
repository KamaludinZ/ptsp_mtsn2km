<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Resources\TicketResource;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Stats overview kondisi layanan: where every request currently sits in the
 * workflow. Shared by all staff roles so the whole office reads one picture.
 */
class ServiceConditionOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected ?string $heading = 'Kondisi Layanan';

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getDescription(): ?string
    {
        return 'Periode: ' . ServiceMetrics::PERIODS['all'] . ' s.d. ' . now()->translatedFormat('j F Y, H:i') . ' — posisi seluruh permohonan dalam alur layanan.';
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $tickets = ServiceMetrics::tickets();
        $status = $tickets['by_status'];
        $n = fn (int $value) => number_format($value, 0, ',', '.');

        // Cards link to where the signed-in role acts on them; roles without
        // access to the ticket list just read the numbers.
        $user = auth()->user();
        $list = fn (string $tab) => TicketResource::canViewAny() ? TicketResource::getUrl('index', ['activeTab' => $tab]) : null;
        $queue = $user?->can('backoffice.access') ? $list('antrian') : $list('semua');
        $disposition = Approvals::canAccess() ? Approvals::getUrl() : $list('persetujuan');

        if ($tickets['total'] === 0) {
            return [
                Stat::make('Belum ada permohonan', '0')
                    ->description('Statistik kondisi layanan akan muncul setelah permohonan pertama masuk lewat portal atau loket.')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
            ];
        }

        return [
            Stat::make('Menunggu verifikasi', $n($status['submitted']))
                ->description('Baru diajukan pemohon')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($status['submitted'] ? 'info' : 'gray')
                ->url($queue),
            Stat::make('Sedang diproses', $n($status['verified'] + $status['in_process']))
                ->description($n($status['verified']) . ' terverifikasi · ' . $n($status['in_process']) . ' diproses')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->url($queue),
            Stat::make('Menunggu disposisi', $n($tickets['awaiting_approval']))
                ->description('Perlu keputusan pimpinan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($tickets['awaiting_approval'] ? 'warning' : 'gray')
                ->url($disposition),
            Stat::make('Siap diambil', $n($tickets['ready_for_pickup']))
                ->description('Hasil layanan menunggu diserahkan')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color($tickets['ready_for_pickup'] ? 'success' : 'gray')
                ->url($list('siap-diambil')),
            Stat::make('Selesai', $n($tickets['completed']))
                ->description(($tickets['completion_rate'] ?? 0) . '% dari ' . $n($tickets['total']) . ' permohonan')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url($list('semua')),
            Stat::make('Melewati target', $n($tickets['overdue']))
                ->description($tickets['on_time_rate'] !== null ? $tickets['on_time_rate'] . '% selesai tepat waktu' : 'Belum ada data ketepatan waktu')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tickets['overdue'] ? 'danger' : 'gray')
                ->url($list('terlambat')),
        ];
    }
}
