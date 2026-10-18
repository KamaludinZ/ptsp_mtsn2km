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

    private function delivery(string $channel, string $status, ?string $error = null, int $hoursAgo = 1, string $event = 'ticket_created'): void
    {
        $delivery = NotificationDelivery::create(['event' => $event, 'channel' => $channel, 'recipient' => 'x', 'body' => 'Halo', 'status' => $status, 'error' => $error, 'attempts' => 1]);
        $delivery->forceFill(['created_at' => now()->subHours($hoursAgo)])->saveQuietly();
    }

    public function test_channels_show_their_switch_and_last_day_deliveries(): void
    {
        NotificationSetting::updateOrCreate(['channel' => 'whatsapp'], ['is_enabled' => true]);
        $this->delivery('whatsapp', 'sent');
        $this->delivery('whatsapp', 'failed', 'HTTP 500 dari gateway');
        $this->delivery('whatsapp', 'sent', hoursAgo: 30); // older than a day
        $this->delivery('email', 'sent');
        // Pengaduan messages count on their own line.
        NotificationSetting::store('email', true, [], line: 'pengaduan');
        $this->delivery('email', 'failed', 'SMTP aduan menolak', event: 'complaint_new');

        $integrations = app(SystemMonitorService::class)->integrations();
        $this->assertSame(['email', 'whatsapp', 'pengaduan_email', 'pengaduan_whatsapp'], array_keys($integrations));
        $this->assertSame(['label' => 'WhatsApp · Pelayanan', 'enabled' => true, 'sent' => 1, 'failed' => 1], array_intersect_key($integrations['whatsapp'], array_flip(['label', 'enabled', 'sent', 'failed'])));
        $this->assertSame('HTTP 500 dari gateway', $integrations['whatsapp']['last_failure']['error']);
        $this->assertFalse($integrations['email']['enabled']);
        $this->assertSame(1, $integrations['email']['sent']);
        $this->assertSame(0, $integrations['email']['failed']);
        $this->assertSame(['enabled' => true, 'sent' => 0, 'failed' => 1], array_intersect_key($integrations['pengaduan_email'], array_flip(['enabled', 'sent', 'failed'])));
        $this->assertSame('SMTP aduan menolak', $integrations['pengaduan_email']['last_failure']['error']);
        $this->assertFalse($integrations['pengaduan_whatsapp']['enabled']);

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)->set('tab', 'aplikasi')
            ->assertSeeHtml('data-integrations')
            ->assertSee('Integrasi notifikasi')
            ->assertSeeInOrder(['Integrasi notifikasi', 'Nonaktif', '0 gagal', 'Aktif, ada kegagalan', '1 gagal', 'HTTP 500 dari gateway']);
    }

    public function test_status_through_the_api_for_admins_only(): void
    {
        NotificationSetting::updateOrCreate(['channel' => 'email'], ['is_enabled' => true]);
        $this->delivery('email', 'failed', 'SMTP menolak');

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        \Laravel\Sanctum\Sanctum::actingAs($admin);

        $this->getJson('/api/monitoring/integrasi')->assertOk()
            ->assertJsonPath('data.0.kanal', 'email')
            ->assertJsonPath('data.0.aktif', true)
            ->assertJsonPath('data.0.gagal_24_jam', 1)
            ->assertJsonPath('data.0.gagal_terakhir.galat', 'SMTP menolak')
            ->assertJsonPath('data.0.jalur', 'pelayanan')
            ->assertJsonPath('data.1.kanal', 'whatsapp')
            ->assertJsonPath('data.1.gagal_terakhir', null)
            ->assertJsonPath('data.2.kanal', 'email')
            ->assertJsonPath('data.2.jalur', 'pengaduan')
            ->assertJsonPath('data.2.aktif', false);

        $officer = User::factory()->create();
        $officer->assignRole(Role::findOrCreate('front_desk', 'web'));
        \Laravel\Sanctum\Sanctum::actingAs($officer);
        $this->getJson('/api/monitoring/integrasi')->assertForbidden();
    }
}
