<?php

namespace Tests\Unit;

use App\Services\SystemMonitorService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** The running version: APP_VERSION (Docker build), then RELEASE (release ZIP), then git. */
class AppVersionTest extends TestCase
{
    private string $release;

    protected function setUp(): void
    {
        parent::setUp();

        $this->release = base_path('RELEASE');
        $this->assertFileDoesNotExist($this->release, 'A RELEASE file exists in the project root; remove it before testing.');
        Cache::forget('monitor:version');
    }

    protected function tearDown(): void
    {
        @unlink($this->release);

        parent::tearDown();
    }

    public function test_configured_version_wins(): void
    {
        config(['app.version' => 'v2.0.0']);
        file_put_contents($this->release, "v1.9.0\n");

        $this->assertSame('v2.0.0', app(SystemMonitorService::class)->version());
    }

    public function test_release_file_of_a_release_zip_is_used_without_configuration(): void
    {
        config(['app.version' => null]);
        file_put_contents($this->release, "v1.9.0\n");

        $this->assertSame('v1.9.0', app(SystemMonitorService::class)->version());
    }

    public function test_monitor_flags_missing_editor_key(): void
    {
        config(['tinymce.api_key' => null]);
        $titles = array_column(app(SystemMonitorService::class)->findings(), 'title');
        $this->assertContains('Editor teks belum aktif', $titles);

        config(['tinymce.api_key' => 'k']);
        $this->assertNotContains('Editor teks belum aktif', array_column(app(SystemMonitorService::class)->findings(), 'title'));
    }
}
