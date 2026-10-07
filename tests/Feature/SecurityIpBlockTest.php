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
