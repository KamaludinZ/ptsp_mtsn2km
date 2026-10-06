<?php

namespace Tests\Feature;

use App\Filament\Pages\System\ActivityTrail;
use App\Filament\Pages\System\SystemMonitor;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Halaman Rekam Jejak Aktivitas (activity_log), hanya untuk administrator. */
class ActivityTrailPageTest extends TestCase
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
        $this->sari = User::factory()->create(['name' => 'Bu Sari']);
        $this->hadi = User::factory()->create(['name' => 'Pak Hadi']);
        foreach (['kepala_tu', 'front_desk'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        $this->sari->assignRole(['kepala_tu', 'front_desk']);
        $this->hadi->assignRole('front_desk');

        $this->travelTo(now()->subDay()->setTime(7, 31));
        activity('role')->causedBy($this->hadi)->event('chosen')->withProperties(['ke' => 'front_desk', 'peran_aktif' => 'front_desk', 'sumber' => 'web', 'halaman' => '/cp/pilih-peran'])->log('Memilih peran Front Desk saat masuk');
        $this->travelTo(now()->setTime(14, 5));
        activity('ticket')->causedBy($this->hadi)->event('picked_up')->withProperties(['tiket' => 'PTSP-0131', 'layanan' => 'Surat Rekomendasi', 'peran_aktif' => 'front_desk'])->log('Menyerahkan produk layanan · PTSP-0131');
        $this->travelBack();
        $this->travelTo(now()->setTime(9, 15));
        activity('impersonation')->causedBy($this->admin)->event('started')->withProperties(['akun_dipakai' => 'Budi Santoso', 'alasan' => 'Meninjau keluhan unggah berkas', 'peran_aktif' => 'admin'])->log('Mulai ganti akun ke Budi Santoso');
        $this->travelTo(now()->setTime(10, 40));
        activity('role')->causedBy($this->sari)->event('switched')->withProperties(['dari' => 'front_desk', 'ke' => 'kepala_tu', 'peran_aktif' => 'kepala_tu', 'sumber' => 'web', 'halaman' => '/cp/tiket/5'])->log('Berpindah peran Front Desk → Kepala Tata Usaha');
        $this->travelTo(now()->setTime(10, 42));
        activity('ticket')->causedBy($this->sari)->event('approved')->withProperties(['tiket' => 'PTSP-0142', 'layanan' => 'Surat Keterangan Aktif', 'peran_aktif' => 'kepala_tu', 'impersonated_by' => ['admin' => 'Admin PTSP']])->log('Mendisposisikan permohonan · PTSP-0142');
        $this->travelBack();
        activity('audit')->causedBy($this->admin)->log('Bukan rekam jejak');
    }

    private function trail(): \Livewire\Features\SupportTesting\Testable
    {
        $this->actingAs($this->admin);

        return Livewire::test(ActivityTrail::class);
    }

    public function test_admin_sees_the_trail_grouped_by_day(): void
    {
        $this->actingAs($this->admin)
            ->get(ActivityTrail::getUrl())
            ->assertOk()
            ->assertSeeInOrder([now()->translatedFormat('l, j F Y'), 'Mendisposisikan permohonan', 'sebagai Kepala Tata Usaha', now()->subDay()->translatedFormat('l, j F Y')])
            ->assertSee('Front Desk → Kepala Tata Usaha')
            ->assertSee('lewat ganti akun')
            ->assertDontSee('Bukan rekam jejak');
    }

    public function test_filter_by_type(): void
    {
        $this->trail()
            ->call('setType', 'role_switch')
            ->assertSeeHtml('data-trail-type="role_switch"')
            ->assertDontSeeHtml('data-trail-type="ticket"')
            ->call('setType', 'bukan-jenis')
            ->assertSet('type', '');
    }

    public function test_filter_by_user_role_date_and_action(): void
    {
        $this->trail()
            ->set('filters.user', $this->sari->id)
            ->assertSee('Mendisposisikan permohonan')
            ->assertDontSee('Menyerahkan produk layanan')
            ->set('filters.user', null)
            ->set('filters.role', 'front_desk')
            ->assertSee('Menyerahkan produk layanan')
            ->assertDontSee('Mendisposisikan permohonan')
            ->set('filters.role', null)
            ->set('filters.from', now()->toDateString())
            ->assertSee('Mendisposisikan permohonan')
            ->assertDontSee('Memilih peran Front Desk saat masuk')
            ->set('filters.action', 'GANTI AKUN')
            ->assertSee('Mulai ganti akun')
            ->assertDontSee('Mendisposisikan permohonan')
            ->call('resetFilters')
            ->assertSee('Memilih peran Front Desk saat masuk');
    }

    public function test_recap_per_user_and_role_and_tab_counts(): void
    {
        $this->trail()
            ->assertSeeHtml('data-recap-row="Bu Sari|Kepala Tata Usaha"')
            ->assertSeeHtml('data-recap-row="Pak Hadi|Front Desk"')
            ->assertViewHas('recap', function ($recap) {
                $sari = $recap->firstWhere('user', 'Bu Sari');

                return $sari['total'] === 2 && $sari['by_type'] == ['ticket' => 1, 'role_switch' => 1];
            })
            ->assertViewHas('counts', fn ($counts) => $counts->all() == ['role_switch' => 2, 'ticket' => 2, 'impersonation' => 1])
            ->set('filters.user', $this->hadi->id)
            ->assertDontSeeHtml('data-recap-row="Bu Sari|Kepala Tata Usaha"');
    }

    public function test_detail_of_an_entry(): void
    {
        $started = Activity::inLog('impersonation')->sole();
        $switched = Activity::inLog('role')->where('event', 'switched')->sole();

        $this->trail()
            ->call('showDetail', $started->id)
            ->assertDispatched('open-modal', id: 'trail-detail')
            ->assertSeeHtml('data-trail-detail="' . $started->id . '"')
            ->assertSeeInOrder(['Akun dipakai', 'Budi Santoso', 'Alasan', 'Meninjau keluhan unggah berkas'])
            ->call('showDetail', $switched->id)
            ->assertSeeInOrder(['Perpindahan', 'Front Desk', 'Kepala Tata Usaha', 'Halaman'])
            ->assertViewHas('detail', fn ($detail) => $detail['details']['Halaman'] === '/cp/tiket/5')
            ->call('showDetail', Activity::inLog('audit')->sole()->id)
            ->assertSet('detailId', null);
    }

    public function test_only_administrators_in_the_admin_role(): void
    {
        $officer = User::factory()->create();
        $officer->assignRole('front_desk');
        $this->actingAs($officer)->get(ActivityTrail::getUrl())->assertForbidden();

        // An administrator working in another role sees neither the trail nor the monitoring logs.
        $multi = User::factory()->create();
        $multi->assignRole(['admin', 'front_desk']);
        $multi->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        $this->actingAs($multi->fresh())->withSession([ActiveRoles::SESSION_KEY => 'front_desk'])
            ->get(ActivityTrail::getUrl())->assertForbidden();
        $this->assertFalse(SystemMonitor::canAccess());

        Sanctum::actingAs($multi->fresh());
        $this->getJson('/api/monitoring/log?jenis=audit')->assertForbidden();
        $this->getJson('/api/rekam-jejak')->assertForbidden();
    }
}
