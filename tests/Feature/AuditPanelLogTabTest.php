<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Tab Log & Audit: bagian perpindahan peran & ganti akun (admin saja). */
class AuditPanelLogTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_tab_shows_role_and_account_switch_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->assertSeeHtml('data-role-audit')
            ->assertSee('Perpindahan peran & ganti akun')
            ->assertSeeHtml('data-role-audit-card="open_sessions"')
            ->assertSeeInOrder(['Perpindahan peran hari ini', 'Sesi ganti akun berjalan', 'Sesi ganti akun 7 hari', 'Aksi lewat ganti akun'])
            ->assertSee('Buka Rekam Jejak Aktivitas')
            // The existing sections stay.
            ->assertSee('Log aplikasi')
            ->assertSee('Audit trail aktivitas pengguna');
    }

    public function test_recap_table_per_user_and_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->assertSeeHtml('data-role-audit-recap')
            ->assertSeeInOrder(['Rekap per pengguna & peran aktif', 'Pindah peran', 'Sesi ganti akun', 'Aksi tiket', 'Lewat ganti akun'])
            ->assertSeeInOrder(['Bu Sari', 'Kepala Tata Usaha', 'Bu Sari', 'Front Desk']);
    }

    public function test_filters_and_search(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->assertSeeHtml('data-role-audit-filters')
            ->set('roleAuditFilters.jenis', 'impersonation')
            ->assertSee('Mulai ganti akun ke Budi Santoso')
            ->assertDontSee('Memilih peran Front Desk saat masuk')
            ->set('roleAuditFilters.jenis', '')
            ->set('roleAuditFilters.pengguna', 'Pak Hadi')
            ->assertSee('Memilih peran Front Desk saat masuk')
            ->assertDontSee('Mulai ganti akun ke Budi Santoso')
            ->assertViewHas('roleAudit', fn ($a) => count($a['recap']) === 1 && $a['filtering'])
            ->call('resetRoleAuditFilters')
            ->set('roleAuditFilters.cari', 'sari wulandari')
            ->assertSee('Mengakhiri ganti akun Sari Wulandari')
            ->assertDontSee('Memilih peran Front Desk saat masuk')
            ->call('resetRoleAuditFilters')
            ->set('roleAuditFilters.dari', now()->subDay()->toDateString())
            ->assertDontSee('Mengakhiri ganti akun Sari Wulandari');
    }

    public function test_hidden_through_a_borrowed_admin_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        $this->actingAs($admin)->post(route('ganti-akun.mulai', $otherAdmin), ['alasan' => 'Meninjau tampilan admin lain'])->assertRedirect();
        $this->assertAuthenticatedAs($otherAdmin);

        $this->assertFalse(SystemMonitor::canSeeRoleAudit());
        $this->assertFalse(\App\Filament\Pages\System\ActivityTrail::canAccess());
        Livewire::test(SystemMonitor::class)->set('tab', 'log')
            ->assertDontSeeHtml('data-role-audit')
            ->assertSee('Log aplikasi');
    }

    public function test_other_tabs_do_not_load_it(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin);

        Livewire::test(SystemMonitor::class)->set('tab', 'server')->assertDontSeeHtml('data-role-audit');
    }
}
