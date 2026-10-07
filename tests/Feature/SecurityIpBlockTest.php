<?php

namespace Tests\Feature;

use App\Filament\Pages\System\Security;
use App\Models\User;
use App\Support\SecurityMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Keamanan Sistem: tabel IP diblokir, form blokir, dan buka blokir. */
class SecurityIpBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);
    }

    private function blocked(): array
    {
        return array_keys(app(SecurityMonitor::class)->blockedIps());
    }

    public function test_admin_blocks_an_ip_and_sees_it_listed(): void
    {
        Livewire::test(Security::class)
            ->callAction('blockIp', ['ip' => '203.0.113.9', 'reason' => 'Percobaan masuk berulang', 'hours' => 24])
            ->assertHasNoActionErrors()
            ->assertNotified('203.0.113.9 diblokir.')
            ->assertSee('203.0.113.9');

        $this->assertContains('203.0.113.9', $this->blocked());
    }

    public function test_form_validates_the_address_and_refuses_your_own(): void
    {
        Livewire::test(Security::class)
            ->callAction('blockIp', ['ip' => 'bukan-ip', 'reason' => 'x'])
            ->assertHasActionErrors(['ip' => 'ip']);
        Livewire::test(Security::class)
            ->callAction('blockIp', ['ip' => request()->ip(), 'reason' => 'Uji'])
            ->assertHasActionErrors(['ip']);
        Livewire::test(Security::class)
            ->callAction('blockIp', ['ip' => '203.0.113.10'])
            ->assertHasActionErrors(['reason' => 'required']);

        $this->assertNotContains('203.0.113.10', $this->blocked());
    }

    public function test_blocks_live_in_the_database_and_survive_clearing_the_cache(): void
    {
        $security = app(SecurityMonitor::class);
        $security->blockIp('203.0.113.20', 'Serangan berulang', null, 'admin@example.test');

        $this->assertDatabaseHas('blocked_ips', ['ip' => '203.0.113.20', 'reason' => 'Serangan berulang', 'expires_at' => null]);

        \Illuminate\Support\Facades\Cache::flush();
        $this->assertNotNull($security->isBlocked('203.0.113.20'));

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])->get('/')->assertForbidden();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21'])->get('/')->assertOk();
    }

    public function test_expired_blocks_no_longer_apply(): void
    {
        $security = app(SecurityMonitor::class);
        $security->blockIp('203.0.113.30', 'Sementara', 1, 'admin@example.test');
        $this->assertNotNull($security->isBlocked('203.0.113.30'));

        $this->travel(2)->hours();

        $this->assertNull($security->isBlocked('203.0.113.30'));
        $this->assertNotContains('203.0.113.30', $this->blocked());
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.30'])->get('/')->assertOk();
    }

    public function test_blocks_kept_only_in_the_old_cache_are_carried_over(): void
    {
        \Illuminate\Support\Facades\DB::table('blocked_ips')->delete();
        \Illuminate\Support\Facades\Cache::put('blocked_ips', [
            '198.51.100.7' => ['reason' => 'Lama', 'blocked_at' => now()->subDay()->toDateTimeString(), 'blocked_by' => 'admin', 'expires_at' => null],
            '198.51.100.8' => ['reason' => 'Kedaluwarsa', 'blocked_at' => now()->subDays(3)->toDateTimeString(), 'blocked_by' => 'admin', 'expires_at' => now()->subDay()->toDateTimeString()],
        ]);

        (require database_path('migrations/2026_10_08_140000_create_blocked_ips_table.php'))->down();
        (require database_path('migrations/2026_10_08_140000_create_blocked_ips_table.php'))->up();

        $this->assertSame(['198.51.100.7'], $this->blocked());
    }

    public function test_block_and_unblock_through_the_api(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs(auth()->user());

        $this->postJson('/api/monitoring/ip-diblokir', ['ip' => '192.0.2.44', 'alasan' => 'Pemindaian port', 'jam' => 12])
            ->assertCreated()
            ->assertJsonPath('message', '192.0.2.44 diblokir.')
            ->assertJsonPath('blokir.reason', 'Pemindaian port');

        $this->getJson('/api/monitoring/ip-diblokir')->assertOk()
            ->assertJsonFragment(['ip' => '192.0.2.44', 'alasan' => 'Pemindaian port', 'permanen' => false]);

        $this->postJson('/api/monitoring/ip-diblokir', ['ip' => 'bukan-ip', 'alasan' => 'x'])->assertUnprocessable()->assertJsonValidationErrors('ip');
        $this->postJson('/api/monitoring/ip-diblokir', ['ip' => '127.0.0.1', 'alasan' => 'Uji'])
            ->assertUnprocessable()->assertJsonValidationErrors(['ip' => 'Alamat ini adalah IP Anda sendiri.']);

        $this->deleteJson('/api/monitoring/ip-diblokir/192.0.2.44')->assertOk()->assertJsonPath('message', 'Blokir 192.0.2.44 dibuka.');
        $this->deleteJson('/api/monitoring/ip-diblokir/192.0.2.44')->assertNotFound();
        $this->assertNotContains('192.0.2.44', $this->blocked());

        $this->assertSame(['Memblokir IP 192.0.2.44', 'Membuka blokir IP 192.0.2.44'],
            \Spatie\Activitylog\Models\Activity::inLog('audit')->where('description', 'like', '%192.0.2.44')->oldest('id')->pluck('description')->all());
    }

    public function test_other_staff_cannot_use_the_block_api(): void
    {
        $officer = User::factory()->create();
        $officer->assignRole(Role::findOrCreate('front_desk', 'web'));
        \Laravel\Sanctum\Sanctum::actingAs($officer);

        $this->postJson('/api/monitoring/ip-diblokir', ['ip' => '192.0.2.45', 'alasan' => 'Uji'])->assertForbidden();
        $this->getJson('/api/monitoring/ip-diblokir')->assertForbidden();
    }

    public function test_admin_unblocks_an_ip(): void
    {
        app(SecurityMonitor::class)->blockIp('198.51.100.4', 'Uji', null, 'admin@example.test');

        Livewire::test(Security::class)
            ->call('unblock', '198.51.100.4')
            ->assertNotified('Blokir 198.51.100.4 dibuka.')
            ->call('unblock', '198.51.100.4')
            ->assertNotified('198.51.100.4 tidak ada di daftar blokir.');

        $this->assertNotContains('198.51.100.4', $this->blocked());
    }
}
