<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Models\AppUpdate;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use App\Support\SecurityMonitor;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Monitoring Sistem (admin only). */
class SystemMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_admin_opens_the_summary(): void
    {
        $this->actingAs($this->admin())
            ->get(SystemMonitor::getUrl())
            ->assertOk()
            ->assertSee('Status aplikasi')
            ->assertSee('Database')
            ->assertSee('Terhubung')
            ->assertSee('Skor keamanan');
    }

    public function test_other_staff_cannot_open_monitoring(): void
    {
        $this->actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail())
            ->get(SystemMonitor::getUrl())
            ->assertForbidden();
    }

    public function test_every_tab_renders(): void
    {
        $this->actingAs($this->admin());

        foreach (array_keys(SystemMonitor::TABS) as $tab) {
            Livewire::test(SystemMonitor::class)->set('tab', $tab)->assertOk()->assertSet('tab', $tab);
        }
        Livewire::test(SystemMonitor::class)->set('tab', 'bogus')->assertSet('tab', 'ringkasan');
    }

    public function test_scheduler_heartbeat_is_detected(): void
    {
        $monitor = app(SystemMonitorService::class);
        $this->assertFalse($monitor->scheduler()['ok']);

        SystemMonitorService::beat();

        $this->assertTrue($monitor->scheduler()['ok']);
    }

    public function test_helpers_format_sizes_and_durations(): void
    {
        $this->assertSame('1,5 GB', SystemMonitorService::bytes(1610612736));
        $this->assertSame('2 hari 3 jam 4 menit', SystemMonitorService::duration(2 * 86400 + 3 * 3600 + 4 * 60));
        $this->assertSame('–', SystemMonitorService::bytes(null));
    }

    public function test_application_tab_shows_health_details(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(SystemMonitor::class)->set('tab', 'aplikasi')
            ->assertSee('Kondisi aplikasi')
            ->assertSee('Koneksi database')
            ->assertSee('Latensi')
            ->assertSee('Scheduler')
            ->assertSee(app(SystemMonitorService::class)->version());
    }

    public function test_server_tab_shows_meters_with_thresholds(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(SystemMonitor::class)->set('tab', 'server')
            ->assertSee('Kondisi server')
            ->assertSee('Peringatan mulai 75%, kritis mulai 90%.')
            ->assertSee('Memori (RAM)')
            ->assertSee('Uptime');

        $this->assertSame('success', SystemMonitorService::level(40));
        $this->assertSame('warning', SystemMonitorService::level(80));
        $this->assertSame('danger', SystemMonitorService::level(95));
        $this->assertSame('gray', SystemMonitorService::level(null));
    }

    public function test_security_tab_counts_failed_logins_and_lists_findings(): void
    {
        config(['app.debug' => true]);
        event(new Failed('web', null, ['email' => 'ptsp@mtsn2malang.sch.id', 'password' => 'salah-sekali']));

        $this->actingAs($this->admin());
        Livewire::test(SystemMonitor::class)->set('tab', 'keamanan')
            ->assertSee('Login gagal hari ini')
            ->assertSee('Temuan konfigurasi berisiko')
            ->assertSee('Mode debug aktif')
            ->assertSee('pt***@mtsn2malang.sch.id')
            ->assertDontSee('ptsp@mtsn2malang.sch.id</td>', false);

        $this->assertSame(1, app(SecurityMonitor::class)->metrics()['failed_logins_today']);
    }

    public function test_log_tab_filters_log_entries_and_searches_the_audit_trail(): void
    {
        $this->actingAs($this->admin());
        Log::error('Gateway WhatsApp menolak permintaan uji-log-123');
        Log::info('Info rutin uji-log-456');
        activity('audit')->causedBy($this->admin())->log('Mengubah pengaturan uji-audit-789');

        Livewire::test(SystemMonitor::class)->set('tab', 'log')
            ->assertSee('uji-log-123')
            ->set('logLevel', 'error')
            ->assertSee('uji-log-123')
            ->assertDontSee('uji-log-456')
            ->set('logLevel', '')
            ->set('logSearch', 'uji-log-456')
            ->assertSee('uji-log-456')
            ->assertDontSee('uji-log-123')
            ->assertSee('Mengubah pengaturan uji-audit-789')
            ->set('auditSearch', 'tidak-ada-yang-cocok')
            ->assertDontSee('Mengubah pengaturan uji-audit-789');
    }

    public function test_update_tab_compares_with_github_and_lists_history(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['api.github.com/*' => Http::response(['tag_name' => 'v9.0.0', 'name' => 'Rilis 9', 'html_url' => 'https://github.com/x/y/releases/v9.0.0', 'body' => 'Perbaikan penting', 'published_at' => now()->toIso8601String()])]);
        AppUpdate::create(['source' => 'github', 'from_version' => 'v1.0.0', 'to_version' => 'v1.1.0', 'status' => 'applied', 'performed_by' => $this->admin()->id]);
        $this->actingAs($this->admin());

        Livewire::test(SystemMonitor::class)->set('tab', 'update')
            ->assertSee('v9.0.0')
            ->assertSee('Pembaruan tersedia')
            ->assertSee('v1.0.0 → v1.1.0')
            ->assertSee('Diterapkan')
            ->call('checkForUpdate')
            ->assertNotified('Rilis terbaru: v9.0.0');

        $updates = app(UpdateService::class);
        $this->assertFalse($updates->isNewer('v1.0.0', 'v1.0.0-37-g2b44d7f8'));
        $this->assertTrue($updates->isNewer('v1.1.0', 'v1.0.0-37-g2b44d7f8'));
        $this->assertNull($updates->isNewer('v1.1.0', 'tidak diketahui'));
    }

    public function test_update_tab_explains_when_github_has_no_release(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['api.github.com/*' => Http::response(['message' => 'Not Found'], 404)]);
        $this->actingAs($this->admin());

        Livewire::test(SystemMonitor::class)->set('tab', 'update')
            ->assertSee('Belum ada rilis di GitHub')
            ->assertSee('Belum ada pembaruan yang tercatat.');
    }

    public function test_monitoring_menu_is_only_shown_to_admins(): void
    {
        $this->actingAs($this->admin())->get('/cp')->assertOk()->assertSee(SystemMonitor::getUrl(), false);
    }

    public function test_monitoring_menu_is_hidden_from_other_staff(): void
    {
        $this->actingAs(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail())
            ->get('/cp')->assertOk()->assertDontSee(SystemMonitor::getUrl(), false);
    }

    public function test_overall_status_badge_and_last_check_time(): void
    {
        $this->actingAs($this->admin());
        $monitor = app(SystemMonitorService::class);

        // No scheduler heartbeat yet: needs attention.
        $overall = $monitor->overall();
        $this->assertContains('Scheduler tidak terdeteksi', $overall['issues']);
        $this->assertNotSame('success', $overall['level']);

        Livewire::test(SystemMonitor::class)
            ->assertSee('Status sistem: ' . $overall['label'])
            ->assertSee('Pengecekan terakhir')
            ->assertSee('Dicek');

        $this->assertNotNull(SystemMonitor::getNavigationBadge());
        $this->assertStringContainsString('Scheduler tidak terdeteksi', SystemMonitor::getNavigationBadgeTooltip());

        $app = $monitor->application();
        $app['scheduler']['ok'] = true;
        $calm = ['load_percent' => 10.0, 'memory' => ['used_percent' => 20.0], 'disk' => ['used_percent' => 30.0]];
        $this->assertSame('Sehat', $monitor->overall($app, $calm)['label']);
        $this->assertSame('Kritis', $monitor->overall($app, ['disk' => ['used_percent' => 97.0]] + $calm)['label']);
    }
}
