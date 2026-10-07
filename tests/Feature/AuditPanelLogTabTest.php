<?php

namespace Tests\Feature;

use App\Filament\Pages\System\SystemMonitor;
use App\Models\ImpersonationSession;
use App\Models\User;
use App\Services\AuditPanelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Tab Log & Audit: perpindahan peran & ganti akun (admin saja), dari rekam jejak nyata. */
class AuditPanelLogTabTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sari;

    private User $hadi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin PTSP']);
        $this->admin->assignRole(Role::findOrCreate('admin', 'web'));
        foreach (['kepala_tu', 'front_desk'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        $this->sari = User::factory()->create(['name' => 'Bu Sari']);
        $this->sari->assignRole(['kepala_tu', 'front_desk']);
        $this->hadi = User::factory()->create(['name' => 'Pak Hadi']);
        $this->hadi->assignRole('front_desk');

        activity('role')->causedBy($this->sari)->event('switched')->withProperties(['dari' => 'front_desk', 'ke' => 'kepala_tu', 'peran_aktif' => 'kepala_tu'])->log('Berpindah peran Front Desk → Kepala Tata Usaha');
        activity('ticket')->causedBy($this->sari)->event('approved')->withProperties(['tiket' => 'PTSP-1', 'peran_aktif' => 'kepala_tu'])->log('Mendisposisikan permohonan · PTSP-1');
        activity('role')->causedBy($this->hadi)->event('chosen')->withProperties(['ke' => 'front_desk', 'peran_aktif' => 'front_desk'])->log('Memilih peran Front Desk saat masuk');
        activity('impersonation')->causedBy($this->admin)->event('started')->withProperties(['akun_dipakai' => 'Budi Santoso', 'peran_aktif' => 'admin'])->log('Mulai ganti akun ke Budi Santoso');
        activity('ticket')->causedBy($this->hadi)->event('file_uploaded')->withProperties(['tiket' => 'PTSP-2', 'peran_aktif' => 'front_desk', 'impersonated_by' => ['admin' => 'Admin PTSP']])->log('Berkas diunggah · PTSP-2');
        ImpersonationSession::create(['admin_id' => $this->admin->id, 'admin_name' => 'Admin PTSP', 'target_name' => 'Budi Santoso', 'reason' => 'Meninjau keluhan', 'started_at' => now()]);

        $this->actingAs($this->admin);
    }

    public function test_summary_comes_from_the_trail(): void
    {
        $this->assertSame(
            ['switches_today' => 2, 'open_sessions' => 1, 'sessions_week' => 1, 'impersonated_actions' => 1],
            app(AuditPanelService::class)->summary(),
        );
    }

    public function test_log_tab_shows_the_panel_with_latest_entries(): void
    {
        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->assertSeeHtml('data-role-audit')
            ->assertSeeInOrder(['Perpindahan peran hari ini', 'Sesi ganti akun berjalan', 'Sesi ganti akun 7 hari', 'Aksi lewat ganti akun'])
            ->assertSee('Mulai ganti akun ke Budi Santoso')
            ->assertSee('Memilih peran Front Desk saat masuk')
            // Ticket actions are in the recap, not in the role/account-switch list.
            ->assertViewHas('roleAudit', fn ($a) => collect($a['latest'])->pluck('type')->unique()->sort()->values()->all() === ['impersonation', 'role_switch'])
            ->assertSee('Log aplikasi')
            ->assertSee('Audit trail aktivitas pengguna');
    }

    public function test_recap_per_user_and_role(): void
    {
        $recap = collect(app(AuditPanelService::class)->recap())->keyBy(fn ($r) => $r['user'] . '|' . $r['role']);

        $this->assertSame(['switches' => 1, 'sessions' => 0, 'ticket_actions' => 1, 'impersonated' => 0], array_intersect_key($recap['Bu Sari|Kepala Tata Usaha'], array_flip(['switches', 'sessions', 'ticket_actions', 'impersonated'])));
        $this->assertSame(1, $recap['Pak Hadi|Front Desk']['impersonated']);
        $this->assertSame(1, $recap['Admin PTSP|Administrator']['sessions']);

        Livewire::test(SystemMonitor::class)->set('tab', 'log')
            ->assertSeeHtml('data-role-audit-recap')
            ->assertSeeInOrder(['Rekap per pengguna & peran aktif', 'Pindah peran', 'Sesi ganti akun', 'Aksi tiket', 'Lewat ganti akun']);
    }

    public function test_filters_and_search(): void
    {
        // The panel's own list (the audit trail section further down lists everything).
        $latest = fn (array $expected) => fn ($a) => collect($a['latest'])->pluck('description')->all() === $expected;

        Livewire::test(SystemMonitor::class)
            ->set('tab', 'log')
            ->assertSeeHtml('data-role-audit-filters')
            ->set('roleAuditFilters.jenis', 'impersonation')
            ->assertViewHas('roleAudit', $latest(['Mulai ganti akun ke Budi Santoso']))
            ->set('roleAuditFilters.jenis', '')
            ->set('roleAuditFilters.pengguna', 'Pak Hadi')
            ->assertViewHas('roleAudit', $latest(['Memilih peran Front Desk saat masuk']))
            ->assertViewHas('roleAudit', fn ($a) => count($a['recap']) === 1 && $a['filtering'])
            ->call('resetRoleAuditFilters')
            ->set('roleAuditFilters.peran', 'Kepala Tata Usaha')
            ->assertViewHas('roleAudit', $latest(['Berpindah peran Front Desk → Kepala Tata Usaha']))
            ->call('resetRoleAuditFilters')
            ->set('roleAuditFilters.cari', 'budi')
            ->assertViewHas('roleAudit', $latest(['Mulai ganti akun ke Budi Santoso']))
            ->call('resetRoleAuditFilters')
            ->set('roleAuditFilters.dari', now()->addDay()->toDateString())
            ->assertViewHas('roleAudit', $latest([]));
    }

    public function test_hidden_through_a_borrowed_admin_account(): void
    {
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        $this->post(route('ganti-akun.mulai', $otherAdmin), ['alasan' => 'Meninjau tampilan admin lain'])->assertRedirect();
        $this->assertAuthenticatedAs($otherAdmin);

        $this->assertFalse(SystemMonitor::canSeeRoleAudit());
        $this->assertFalse(\App\Filament\Pages\System\ActivityTrail::canAccess());
        Livewire::test(SystemMonitor::class)->set('tab', 'log')->assertDontSeeHtml('data-role-audit')->assertSee('Log aplikasi');
    }

    public function test_other_tabs_do_not_load_it(): void
    {
        Livewire::test(SystemMonitor::class)->set('tab', 'server')->assertDontSeeHtml('data-role-audit');
    }
}
