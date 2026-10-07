<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Models\NotificationDelivery;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\SystemMonitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Monitoring Sistem > Aplikasi: panel status integrasi notifikasi. */
class MonitoringIntegrationsPanelTest extends TestCase
{
    use RefreshDatabase;

    private function delivery(string $channel, string $status, ?string $error = null, int $hoursAgo = 1): void
    {
        $delivery = NotificationDelivery::create(['event' => 'ticket_created', 'channel' => $channel, 'recipient' => 'x', 'body' => 'Halo', 'status' => $status, 'error' => $error, 'attempts' => 1]);
        $delivery->forceFill(['created_at' => now()->subHours($hoursAgo)])->saveQuietly();
    }

    public function test_channels_show_their_switch_and_last_day_deliveries(): void
    {
        NotificationSetting::updateOrCreate(['channel' => 'whatsapp'], ['is_enabled' => true]);
        $this->delivery('whatsapp', 'sent');
        $this->delivery('whatsapp', 'failed', 'HTTP 500 dari gateway');
        $this->delivery('whatsapp', 'sent', hoursAgo: 30); // older than a day
        $this->delivery('email', 'sent');

        $integrations = app(SystemMonitorService::class)->integrations();
        $this->assertSame(['label' => 'WhatsApp', 'enabled' => true, 'sent' => 1, 'failed' => 1], array_intersect_key($integrations['whatsapp'], array_flip(['label', 'enabled', 'sent', 'failed'])));
        $this->assertSame('HTTP 500 dari gateway', $integrations['whatsapp']['last_failure']['error']);
        $this->assertFalse($integrations['email']['enabled']);
        $this->assertSame(1, $integrations['email']['sent']);

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)->set('tab', 'aplikasi')
            ->assertSeeHtml('data-integrations')
            ->assertSee('Integrasi notifikasi')
            ->assertSeeInOrder(['Integrasi notifikasi', 'Nonaktif', '0 gagal', 'Aktif, ada kegagalan', '1 gagal', 'HTTP 500 dari gateway']);
    }
}
