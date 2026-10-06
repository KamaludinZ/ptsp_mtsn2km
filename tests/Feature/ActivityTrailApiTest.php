<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** GET /api/rekam-jejak: rekam jejak aktivitas untuk administrator, dengan saringan. */
class ActivityTrailApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sari;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin PTSP']);
        $this->admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->sari = User::factory()->create(['name' => 'Bu Sari']);
        foreach (['kepala_tu', 'front_desk'] as $role) {
            $this->sari->assignRole(Role::findOrCreate($role, 'web'));
        }

        $this->travelTo('2026-10-05 07:30');
        activity('role')->causedBy($this->sari)->event('chosen')->withProperties(['ke' => 'front_desk', 'peran_aktif' => 'front_desk', 'ip' => '10.0.0.7', 'sumber' => 'web'])->log('Memilih peran Front Desk saat masuk');
        $this->travelTo('2026-10-06 10:40');
        activity('role')->causedBy($this->sari)->event('switched')->withProperties(['dari' => 'front_desk', 'ke' => 'kepala_tu', 'peran_aktif' => 'kepala_tu', 'sumber' => 'web'])->log('Berpindah peran Front Desk → Kepala Tata Usaha');
        $this->travelTo('2026-10-06 10:42');
        activity('ticket')->causedBy($this->sari)->event('rejected')->withProperties(['tiket' => 'PTSP-1', 'layanan' => 'Legalisir', 'peran_aktif' => 'kepala_tu', 'status_awal' => 'verified', 'status_akhir' => 'rejected'])->log('Ditolak · PTSP-1');
        activity('impersonation')->causedBy($this->admin)->event('started')->withProperties(['akun_dipakai' => 'Budi', 'alasan' => 'Meninjau keluhan'])->log('Mulai ganti akun ke Budi');
        activity('audit')->causedBy($this->admin)->log('Bukan rekam jejak');
        $this->travelBack();
    }

    private function descriptions(array $query = []): array
    {
        return collect($this->getJson('/api/rekam-jejak?' . http_build_query($query))->assertOk()->json('data'))->pluck('keterangan')->all();
    }

    public function test_lists_trail_entries_newest_first(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/rekam-jejak')->assertOk()
            ->assertJsonPath('total', 4)
            ->assertJsonPath('data.0.jenis', 'impersonation')
            ->assertJsonPath('data.1.tiket', 'PTSP-1')
            ->assertJsonPath('data.1.peran_aktif_label', 'Kepala Tata Usaha')
            ->assertJsonPath('data.1.detail', ['Status awal' => \App\Support\TicketLabels::status('verified'), 'Status akhir' => \App\Support\TicketLabels::status('rejected')])
            ->assertJsonPath('data.2.peran_asal', 'Front Desk')
            ->assertJsonPath('data.2.peran_tujuan', 'Kepala Tata Usaha')
            ->assertJsonPath('data.3.pengguna.nama', 'Bu Sari');
    }

    public function test_filters(): void
    {
        Sanctum::actingAs($this->admin);

        $this->assertSame(['Mulai ganti akun ke Budi'], $this->descriptions(['pengguna' => $this->admin->id]));
        $this->assertSame(['Ditolak · PTSP-1', 'Berpindah peran Front Desk → Kepala Tata Usaha'], $this->descriptions(['peran' => 'kepala_tu']));
        $this->assertSame(['Berpindah peran Front Desk → Kepala Tata Usaha', 'Memilih peran Front Desk saat masuk'], $this->descriptions(['jenis' => 'role_switch']));
        $this->assertSame(['Ditolak · PTSP-1'], $this->descriptions(['aksi' => 'rejected']));
        $this->assertSame(['Memilih peran Front Desk saat masuk'], $this->descriptions(['sampai' => '2026-10-05']));
        $this->getJson('/api/rekam-jejak?jenis=audit')->assertUnprocessable();
    }

    public function test_only_administrators(): void
    {
        $this->getJson('/api/rekam-jejak')->assertUnauthorized();

        $this->sari->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id])->save();
        Sanctum::actingAs($this->sari);
        $this->getJson('/api/rekam-jejak')->assertForbidden();
    }
}
