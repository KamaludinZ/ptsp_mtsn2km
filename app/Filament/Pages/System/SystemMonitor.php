<?php

namespace App\Filament\Pages\System;

use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use App\Support\LogReader;
use App\Support\SecurityMonitor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity;

/**
 * Monitoring Sistem (admin only): application and server health, security
 * indicators, logs & audit, and application updates, one tab each.
 */
class SystemMonitor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Monitoring Sistem';

    protected static ?string $title = 'Monitoring Sistem';

    protected static ?string $slug = 'monitoring';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.system-monitor';

    public const TABS = [
        'ringkasan' => ['Ringkasan', 'heroicon-m-squares-2x2'],
        'aplikasi' => ['Aplikasi', 'heroicon-m-cube'],
        'server' => ['Server', 'heroicon-m-server'],
        'keamanan' => ['Keamanan', 'heroicon-m-shield-check'],
        'log' => ['Log & Audit', 'heroicon-m-document-text'],
        'update' => ['Update Aplikasi', 'heroicon-m-arrow-path'],
    ];

    #[Url]
    public string $tab = 'ringkasan';

    public string $logLevel = '';

    public string $logSearch = '';

    public string $auditSearch = '';

    /** Saringan bagian perpindahan peran & ganti akun: cari, pengguna, peran, jenis, dari, sampai. */
    public array $roleAuditFilters = ['cari' => '', 'pengguna' => '', 'peran' => '', 'jenis' => '', 'dari' => '', 'sampai' => ''];

    /**
     * Perpindahan peran & ganti akun: administrators only, in the admin role,
     * and not while using someone else's account (not even another admin's).
     */
    public static function canSeeRoleAudit(): bool
    {
        $user = auth()->user();

        return $user instanceof \App\Models\User
            && \App\Support\ActiveRoles::hasRole($user, 'admin')
            && ! \App\Support\Impersonation::active();
    }

    public function resetRoleAuditFilters(): void
    {
        $this->roleAuditFilters = array_map(fn () => '', $this->roleAuditFilters);
    }

    /** Administrators working as one: logs and the activity trail are not shown in another active role. */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof \App\Models\User && \App\Support\ActiveRoles::hasRole($user, 'admin');
    }

    public static function getNavigationBadge(): ?string
    {
        $overall = app(SystemMonitorService::class)->cachedOverall();

        return $overall['level'] === 'success' ? null : (string) count($overall['issues']);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return app(SystemMonitorService::class)->cachedOverall()['level'];
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return implode(', ', app(SystemMonitorService::class)->cachedOverall()['issues']) ?: null;
    }

    public function getSubheading(): ?string
    {
        return 'Pengecekan terakhir ' . now()->translatedFormat('j F Y, H:i:s') . ' · diperbarui otomatis setiap 30 detik.';
    }

    public function updatedTab(): void
    {
        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'ringkasan';
        }
    }

    private function updateInfo(SystemMonitorService $monitor): array
    {
        $updates = app(UpdateService::class);
        $current = $monitor->version();
        $release = $updates->latestRelease();

        return [
            'current' => $current,
            'repository' => $updates->repository(),
            'release' => $release,
            'newer' => isset($release['tag']) ? $updates->isNewer($release['tag'], $current) : null,
            'history' => $updates->history(),
        ];
    }

    public function checkForUpdate(): void
    {
        $release = app(UpdateService::class)->latestRelease(fresh: true);

        isset($release['error'])
            ? Notification::make()->title('Pengecekan gagal')->body($release['error'])->warning()->send()
            : Notification::make()->title('Rilis terbaru: ' . $release['tag'])->success()->send();
    }

    /** Latest user activity from the audit trail (Spatie activity log). */
    private function audit()
    {
        $search = trim($this->auditSearch);

        return Activity::query()
            ->with('causer')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('description', 'ilike', "%{$search}%")
                ->orWhere('log_name', 'ilike', "%{$search}%")
                ->orWhere('subject_type', 'ilike', "%{$search}%")
                ->orWhereHasMorph('causer', [User::class], fn ($c) => $c->where('name', 'ilike', "%{$search}%"))))
            ->latest()
            ->limit(50)
            ->get();
    }

    protected function getViewData(): array
    {
        $monitor = app(SystemMonitorService::class);
        $tab = array_key_exists($this->tab, self::TABS) ? $this->tab : 'ringkasan';

        $app = in_array($tab, ['ringkasan', 'aplikasi'], true) ? $monitor->application() : null;
        $server = in_array($tab, ['ringkasan', 'server'], true) ? $monitor->server() : null;
        $overall = $monitor->overall($app, $server);
        Cache::put('monitor:overall', $overall, 60);

        return [
            'overall' => $overall,
            'activeTab' => $tab,
            'tabs' => self::TABS,
            'app' => $app,
            'server' => $server,
            'security' => in_array($tab, ['ringkasan', 'keamanan'], true) ? app(SecurityMonitor::class)->metrics() : null,
            'securityDetail' => $tab === 'keamanan' ? $monitor->security() : null,
            'logs' => $tab === 'log' ? LogReader::entries($this->logLevel ?: null, trim($this->logSearch) ?: null) : null,
            'logFile' => $tab === 'log' ? LogReader::latestFile() : null,
            'audit' => $tab === 'log' ? $this->audit() : null,
            // Perpindahan peran & ganti akun (admin-only panel in the Log & Audit tab).
            'roleAudit' => $tab === 'log' && self::canSeeRoleAudit() ? [
                'summary' => \App\Support\AuditPanelStub::summary(),
                'latest' => \App\Support\AuditPanelStub::latest($this->roleAuditFilters),
                'recap' => \App\Support\AuditPanelStub::recap($this->roleAuditFilters),
                'options' => \App\Support\AuditPanelStub::options(),
                'filtering' => collect($this->roleAuditFilters)->filter(fn ($v) => filled($v))->isNotEmpty(),
            ] : null,
            'update' => $tab === 'update' ? $this->updateInfo($monitor) : null,
        ];
    }
}
