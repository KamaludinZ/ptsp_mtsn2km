<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Models\AppUpdate;
use App\Models\NotificationTemplate;
use App\Models\SystemMetric;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use App\Support\SecurityMonitor;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
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

    public function test_audit_trail_summarises_account_imports_and_exports(): void
    {
        $this->actingAs($this->admin());
        activity('audit')->causedBy($this->admin())->withProperties(['created' => 12, 'failed' => [['row' => 4, 'reason' => 'Email sudah terdaftar.']]])->log(\App\Support\UserAccountAudit::IMPORTED);
        activity('audit')->causedBy($this->admin())->withProperties(['tab' => 'Petugas', 'rows' => 40])->log(\App\Support\UserAccountAudit::EXPORTED);

        Livewire::test(SystemMonitor::class)->set('tab', 'log')
            ->assertSee(['Mengimport akun pengguna', '12 akun dibuat · 1 baris gagal', 'Mengekspor data akun pengguna', 'Tab Petugas · 40 akun'])
            ->assertSee(['Rincian import', 'Baris dibaca', 'Email sudah terdaftar.'])
            ->set('auditSearch', 'akun pengguna')
            ->assertSee('Tab Petugas · 40 akun');
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

    public function test_health_snapshots_are_recorded_and_pruned(): void
    {
        $old = SystemMetric::create(['recorded_at' => now()->subDays(SystemMetric::KEEP_DAYS + 1), 'app_up' => true, 'database_ok' => true, 'scheduler_ok' => true, 'status' => 'success']);

        $metric = app(SystemMonitorService::class)->record();

        $this->assertTrue($metric->database_ok);
        $this->assertContains($metric->status, ['success', 'warning', 'danger']);
        $this->assertModelMissing($old);
    }

    public function test_monitoring_jobs_are_scheduled(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->map(fn ($event) => $event->description);

        $this->assertTrue($events->contains('scheduler-heartbeat'));
        $this->assertTrue($events->contains('monitor-record'));
        $this->assertTrue($events->contains('survey-quarterly-archive'));
    }

    public function test_application_health_api(): void
    {
        app(SystemMonitorService::class)->record();
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/monitoring/aplikasi')
            ->assertOk()
            ->assertJsonPath('aplikasi.database.ok', true)
            ->assertJsonStructure(['status' => ['level', 'label', 'issues'], 'aplikasi' => ['version', 'queue', 'scheduler', 'storage'], 'riwayat'])
            ->assertJsonCount(1, 'riwayat');

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/monitoring/aplikasi')->assertForbidden();
    }

    public function test_server_metrics_api(): void
    {
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/monitoring/server')
            ->assertOk()
            ->assertJsonPath('ambang.kritis', 90)
            ->assertJsonStructure(['server' => ['cpu_cores', 'memory' => ['total', 'used_percent'], 'disk', 'uptime_seconds', 'level' => ['cpu', 'memori', 'disk']], 'riwayat']);
    }

    public function test_security_api(): void
    {
        config(['app.debug' => true]);
        event(new Failed('web', null, ['email' => 'siapa@example.test']));
        Sanctum::actingAs($this->admin());

        $response = $this->getJson('/api/monitoring/keamanan')->assertOk()->assertJsonPath('login_gagal_hari_ini', 1);
        $this->assertContains('Mode debug aktif', collect($response->json('temuan'))->pluck('title')->all());
        $this->assertSame('si***@example.test', $response->json('login_gagal_terbaru.0.email'));
    }

    public function test_every_monitoring_endpoint_is_admin_only(): void
    {
        $routes = collect(app('router')->getRoutes())->filter(fn ($route) => str_starts_with($route->uri(), 'api/monitoring'));
        $this->assertNotEmpty($routes);

        $update = AppUpdate::create(['source' => 'deploy', 'to_version' => 'v1.0.0', 'status' => 'applied']);
        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        foreach ($routes as $route) {
            $this->assertContains('role:admin,sanctum', $route->gatherMiddleware(), $route->uri());
            if (in_array('GET', $route->methods(), true)) {
                $this->getJson('/' . str_replace('{update}', (string) $update->id, $route->uri()))->assertForbidden();
            }
        }
    }

    public function test_log_api_reads_application_access_and_audit_logs(): void
    {
        Log::error('Galat uji-api-log-777');
        event(new \Illuminate\Auth\Events\Login('web', $this->admin(), false));
        activity('audit')->causedBy($this->admin())->log('Mengubah sesuatu uji-audit-888');
        Sanctum::actingAs($this->admin());

        $messages = collect($this->getJson('/api/monitoring/log?level=error&q=uji-api-log')->assertOk()->json('data'))->pluck('message');
        $this->assertTrue($messages->contains(fn ($message) => str_contains($message, 'Galat uji-api-log-777')));

        $access = $this->getJson('/api/monitoring/log?jenis=akses')->assertOk()->json('data');
        $this->assertSame('Masuk', $access[0]['kegiatan']);
        $this->assertSame($this->admin()->name, $access[0]['pengguna']);

        $audit = collect($this->getJson('/api/monitoring/log?jenis=audit&q=uji-audit')->json('data'));
        $this->assertSame(['Mengubah sesuatu uji-audit-888'], $audit->pluck('kegiatan')->all());
    }

    public function test_audit_trail_filters_by_user_and_data_type(): void
    {
        $other = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $template = \App\Models\NotificationTemplate::firstOrFail();
        activity('audit')->causedBy($this->admin())->performedOn($template)->log('Admin mengubah template');
        activity('audit')->causedBy($other)->log('Staf melakukan sesuatu');
        Sanctum::actingAs($this->admin());

        $byUser = collect($this->getJson('/api/monitoring/log?jenis=audit&pengguna=' . $other->id)->assertOk()->json('data'))->pluck('kegiatan');
        $this->assertContains('Staf melakukan sesuatu', $byUser->all());
        $this->assertNotContains('Admin mengubah template', $byUser->all());

        $byData = collect($this->getJson('/api/monitoring/log?jenis=audit&data=NotificationTemplate')->json('data'));
        $this->assertSame(['Admin mengubah template'], $byData->pluck('kegiatan')->all());
    }

    public function test_update_check_api_compares_with_github(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['api.github.com/*' => Http::sequence()
            ->push(['tag_name' => 'v9.0.0', 'name' => 'Rilis 9', 'html_url' => 'https://github.com/x', 'body' => '', 'published_at' => null])
            ->push(['message' => 'Not Found'], 404)]);
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/monitoring/pembaruan')->assertOk()
            ->assertJsonPath('rilis_terbaru.tag', 'v9.0.0')
            ->assertJsonPath('pembaruan_tersedia', true);

        // Cached for 10 minutes unless a fresh check is asked for.
        $this->getJson('/api/monitoring/pembaruan')->assertJsonPath('rilis_terbaru.tag', 'v9.0.0');
        $this->getJson('/api/monitoring/pembaruan?segarkan=1')->assertOk()
            ->assertJsonPath('rilis_terbaru', null)
            ->assertJsonPath('galat', 'Belum ada rilis di GitHub untuk KamaludinZ/ptsp_mtsn2km.');
    }

    /** @param  array<string, string>  $files */
    private function package(array $files): \Illuminate\Http\UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pkg') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return new \Illuminate\Http\UploadedFile($path, 'update.zip', 'application/zip', null, true);
    }

    /** A throwaway code base, so tests never write into the real application. */
    private function sandboxUpdates(): UpdateService
    {
        $target = sys_get_temp_dir() . '/ptsp-update-' . \Illuminate\Support\Str::random(8);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($target . '/app');
        file_put_contents($target . '/composer.json', '{"name":"lama"}');
        file_put_contents($target . '/app/Lama.php', 'lama');
        file_put_contents($target . '/.env', 'RAHASIA=lama');

        $updates = new UpdateService();
        $updates->target = $target;
        $this->app->instance(UpdateService::class, $updates);
        $this->beforeApplicationDestroyed(fn () => \Illuminate\Support\Facades\File::deleteDirectory($target));

        return $updates;
    }

    public function test_upload_rejects_unsafe_or_foreign_packages(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        Sanctum::actingAs($this->admin());

        $this->post('/api/monitoring/pembaruan/unggah', ['berkas' => $this->package(['composer.json' => '{}', '../evil.php' => 'x'])], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonPath('message', 'Paket berisi jalur berkas yang tidak aman: ../evil.php');
        $this->post('/api/monitoring/pembaruan/unggah', ['berkas' => $this->package(['readme.md' => 'x'])], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonPath('message', 'Paket tidak berisi aplikasi (composer.json tidak ditemukan di akar).');
        $this->post('/api/monitoring/pembaruan/unggah', ['berkas' => $this->package(['composer.json' => '{}']), 'checksum' => str_repeat('a', 64)], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonPath('message', 'Checksum SHA-256 tidak cocok: berkas rusak atau bukan paket yang dimaksud.');

        $this->assertSame(0, AppUpdate::count());
    }

    public function test_upload_stores_a_pending_package_with_matching_checksum(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        Sanctum::actingAs($this->admin());
        $file = $this->package(['ptsp-v2/composer.json' => '{"version":"2.0.0"}', 'ptsp-v2/app/Baru.php' => 'baru']);

        $this->post('/api/monitoring/pembaruan/unggah', ['berkas' => $file, 'checksum' => hash_file('sha256', $file->getRealPath())], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('status', 'pending')->assertJsonPath('versi', '2.0.0');

        $update = AppUpdate::sole();
        $this->assertSame('upload', $update->source);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($update->package_path);
    }

    public function test_applying_copies_only_replaceable_files_and_keeps_a_backup(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $updates = $this->sandboxUpdates();
        $update = $updates->storePackage($this->package([
            'rilis/composer.json' => '{"name":"baru"}',
            'rilis/app/Lama.php' => 'baru',
            'rilis/routes/web.php' => 'rute',
            'rilis/.env' => 'RAHASIA=bocor',
            'rilis/storage/app/x.txt' => 'x',
            'rilis/vendor/a.php' => 'x',
            'rilis/catatan.txt' => 'x',
        ]), $this->admin());

        $update = $updates->apply($update, $this->admin(), allowInContainer: true);

        $this->assertSame('applied', $update->status);
        $this->assertSame('baru', file_get_contents($updates->target . '/app/Lama.php'));
        $this->assertSame('rute', file_get_contents($updates->target . '/routes/web.php'));
        $this->assertSame('RAHASIA=lama', file_get_contents($updates->target . '/.env'));
        $this->assertFileDoesNotExist($updates->target . '/storage/app/x.txt');
        $this->assertFileDoesNotExist($updates->target . '/vendor/a.php');
        $this->assertFileDoesNotExist($updates->target . '/catatan.txt');
        $this->assertCount(1, \Illuminate\Support\Facades\Storage::disk('local')->files('update-backups'));

        $this->expectException(\App\Exceptions\TicketActionException::class);
        $updates->apply($update, $this->admin(), allowInContainer: true);
    }

    public function test_packages_changing_dependencies_are_not_applied(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $updates = $this->sandboxUpdates();
        file_put_contents($updates->target . '/composer.lock', '{"lama":1}');
        $update = $updates->storePackage($this->package(['composer.json' => '{}', 'composer.lock' => '{"baru":1}', 'app/Lama.php' => 'baru']), $this->admin());

        $update = $updates->apply($update, $this->admin(), allowInContainer: true);

        $this->assertSame('failed', $update->status);
        $this->assertStringContainsString('composer.lock', $update->notes);
        $this->assertSame('lama', file_get_contents($updates->target . '/app/Lama.php'));
    }

    public function test_apply_api_uses_the_stored_package(): void
    {
        if (app(SystemMonitorService::class)->server()['container']) {
            $this->markTestSkipped('Inside a container the API refuses to apply packages.');
        }

        \Illuminate\Support\Facades\Storage::fake('local');
        $updates = $this->sandboxUpdates();
        Sanctum::actingAs($this->admin());
        $update = $updates->storePackage($this->package(['composer.json' => '{}', 'app/Lama.php' => 'baru']), $this->admin());

        $this->postJson("/api/monitoring/pembaruan/{$update->id}/terapkan")->assertOk()->assertJsonPath('status', 'applied');
        $this->postJson("/api/monitoring/pembaruan/{$update->id}/terapkan")->assertStatus(422);
        $this->assertSame('baru', file_get_contents($updates->target . '/app/Lama.php'));
    }

    public function test_update_upload_is_admin_only(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole('tata_usaha'));

        $this->post('/api/monitoring/pembaruan/unggah', [], ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_redeployed_versions_are_recorded_once(): void
    {
        $updates = app(UpdateService::class);

        $this->assertNull($updates->recordDeployment('tidak diketahui'));
        $first = $updates->recordDeployment('v1.0.0');
        $this->assertSame(['deploy', null, 'v1.0.0', 'applied'], [$first->source, $first->from_version, $first->to_version, $first->status]);
        $this->assertNull($updates->recordDeployment('v1.0.0'));

        $next = $updates->recordDeployment('v1.1.0');
        $this->assertSame(['v1.0.0', 'v1.1.0'], [$next->from_version, $next->to_version]);
        $this->assertSame(2, AppUpdate::where('source', 'deploy')->count());

    }

    public function test_update_history_api_filters_and_paginates(): void
    {
        Sanctum::actingAs($this->admin());
        $this->travelTo(now()->subDays(3));
        AppUpdate::create(['source' => 'deploy', 'from_version' => 'v1.0.0', 'to_version' => 'v1.1.0', 'status' => 'applied', 'finished_at' => now()]);
        $this->travelBack();
        $failed = AppUpdate::create(['source' => 'upload', 'to_version' => '2.0.0', 'status' => 'failed', 'package_name' => 'rilis.zip', 'notes' => 'Gagal.', 'performed_by' => $this->admin()->id]);

        $this->getJson('/api/monitoring/pembaruan/riwayat?per_halaman=1')->assertOk()
            ->assertJsonPath('meta.total', 2)->assertJsonPath('meta.halaman_terakhir', 2)
            ->assertJsonPath('data.0.id', $failed->id);
        $this->getJson('/api/monitoring/pembaruan/riwayat?status=failed')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.paket', 'rilis.zip')
            ->assertJsonPath('data.0.oleh', $this->admin()->name)
            ->assertJsonPath('data.0.sumber_label', 'Unggah berkas')
            ->assertJsonPath('data.0.status_label', 'Gagal');
        $this->getJson('/api/monitoring/pembaruan/riwayat?sumber=deploy')->assertJsonCount(1, 'data')->assertJsonPath('data.0.ke', 'v1.1.0');
        $this->getJson('/api/monitoring/pembaruan/riwayat?dari=' . today()->toDateString())->assertJsonCount(1, 'data');
        $this->getJson('/api/monitoring/pembaruan/riwayat?status=lain')->assertStatus(422);

        $this->getJson("/api/monitoring/pembaruan/{$failed->id}")->assertOk()->assertJsonPath('catatan', 'Gagal.')->assertJsonPath('ke', '2.0.0');
    }

    public function test_update_history_api_is_admin_only(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole('tata_usaha'));

        $this->getJson('/api/monitoring/pembaruan/riwayat')->assertForbidden();
    }

    public function test_collect_command_records_a_snapshot_and_the_running_version(): void
    {
        config(['app.version' => 'v9.9.9']);

        $this->artisan('monitor:collect')->assertSuccessful();

        $this->assertSame(1, SystemMetric::count());
        $this->assertSame('v9.9.9', AppUpdate::where('source', 'deploy')->value('to_version'));
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->first(fn ($e) => $e->description === 'monitor-record');
        $this->assertStringContainsString('monitor:collect', $event->command);
        $this->assertSame('*/5 * * * *', $event->expression);
    }

    public function test_collect_command_alerts_admins_once_when_status_turns_critical(): void
    {
        $this->partialMock(SystemMonitorService::class, function ($mock) {
            $mock->shouldReceive('application')->andReturn([
                'up' => true, 'maintenance' => false, 'database' => ['ok' => false, 'latency_ms' => null, 'driver' => 'pgsql', 'error' => 'x'],
                'storage' => ['writable' => true], 'scheduler' => ['ok' => true, 'last_run' => null], 'queue' => ['pending' => 0, 'failed' => 0],
            ]);
            $mock->shouldReceive('server')->andReturn(['load_percent' => 10.0, 'memory' => ['used_percent' => 10.0], 'disk' => ['used_percent' => 10.0]]);
        });
        Log::spy();

        $this->artisan('monitor:collect')->assertSuccessful();
        $this->artisan('monitor:collect')->assertSuccessful();

        $this->assertSame(['danger', 'danger'], SystemMetric::orderBy('id')->pluck('status')->all());
        Log::shouldHaveReceived('critical')->once()->with('Monitoring: status aplikasi kritis', ['issues' => ['Database tidak terhubung']]);
    }

    public function test_metrics_api_returns_snapshots_and_averages(): void
    {
        Sanctum::actingAs($this->admin());
        foreach ([[now()->subMinutes(10), 20, 'success', true], [now()->subMinutes(5), 40, 'danger', false], [now()->subDays(3), 60, 'warning', true]] as [$at, $cpu, $status, $ok]) {
            SystemMetric::create(['recorded_at' => $at, 'app_up' => true, 'database_ok' => $ok, 'cpu_percent' => $cpu, 'scheduler_ok' => true, 'status' => $status]);
        }

        $this->getJson('/api/monitoring/metrik')->assertOk()
            ->assertJsonPath('jumlah_snapshot', 2)
            ->assertJsonPath('ketersediaan_persen', 50)
            ->assertJsonCount(2, 'titik')
            ->assertJsonPath('titik.1.status', 'danger');
        $this->getJson('/api/monitoring/metrik?rentang=30hari')->assertOk()
            ->assertJsonPath('jumlah_snapshot', 3)
            ->assertJsonPath('titik.0.cpu', 60);
        $this->getJson('/api/monitoring/metrik?rentang=1tahun')->assertStatus(422);
    }
}
