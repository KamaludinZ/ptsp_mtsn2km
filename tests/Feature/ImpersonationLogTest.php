<?php

namespace Tests\Feature;

use App\Filament\Widgets\ImpersonationLog;
use App\Models\ImpersonationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Log ganti akun: API dan tabel, khusus administrator, dengan saringan. */
class ImpersonationLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin Satu']);
        $this->admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->other = User::factory()->create(['name' => 'Admin Dua']);
        $this->other->assignRole('admin');
        $budi = User::factory()->create(['name' => 'Budi']);
        $sari = User::factory()->create(['name' => 'Sari']);

        $this->logSession($this->admin, $budi, 'Meninjau keluhan unggah berkas', '2026-10-01 09:00', '2026-10-01 09:20', 'selesai');
        $this->logSession($this->other, $sari, 'Memeriksa tampilan notifikasi', '2026-10-03 10:00', null, null);
        $this->logSession($this->admin, $sari, 'Membantu isi survei', '2026-10-05 13:00', '2026-10-05 15:00', 'kedaluwarsa');
    }

    private function logSession(User $admin, User $target, string $reason, string $start, ?string $end, ?string $how): void
    {
        ImpersonationSession::create([
            'admin_id' => $admin->id, 'admin_name' => $admin->name,
            'target_id' => $target->id, 'target_name' => $target->name,
            'reason' => $reason, 'started_at' => $start, 'ended_at' => $end, 'end_reason' => $how,
        ]);
    }

    public function test_api_lists_newest_first_with_filters(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/ganti-akun/log')->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('data.0.alasan', 'Membantu isi survei')
            ->assertJsonPath('data.0.durasi_menit', 120)
            ->assertJsonPath('data.0.cara_berakhir_label', 'Ditutup otomatis');

        $names = fn (array $query) => collect($this->getJson('/api/ganti-akun/log?' . http_build_query($query))->assertOk()->json('data'))->pluck('alasan')->all();

        $this->assertSame(['Memeriksa tampilan notifikasi'], $names(['status' => 'berjalan']));
        $this->assertSame(['Membantu isi survei', 'Meninjau keluhan unggah berkas'], $names(['admin' => $this->admin->id]));
        $this->assertSame(['Meninjau keluhan unggah berkas'], $names(['cari' => 'budi']));
        $this->assertSame(['Membantu isi survei'], $names(['cara' => 'kedaluwarsa']));
        $this->assertSame(['Memeriksa tampilan notifikasi'], $names(['dari' => '2026-10-02', 'sampai' => '2026-10-04']));
        $this->getJson('/api/ganti-akun/log?status=lain')->assertUnprocessable();
    }

    public function test_only_administrators_see_it(): void
    {
        $this->getJson('/api/ganti-akun/log')->assertUnauthorized();

        $officer = User::factory()->create();
        $officer->assignRole(Role::findOrCreate('front_desk', 'web'));
        Sanctum::actingAs($officer);
        $this->getJson('/api/ganti-akun/log')->assertForbidden();

        $this->actingAs($officer);
        $this->assertFalse(ImpersonationLog::canView());
    }

    public function test_panel_table_filters(): void
    {
        $this->actingAs($this->admin);
        $open = ImpersonationSession::whereNull('ended_at')->sole();

        Livewire::test(ImpersonationLog::class)
            ->assertCanSeeTableRecords(ImpersonationSession::all())
            ->filterTable('status', 'berjalan')
            ->assertCanSeeTableRecords([$open])
            ->assertCanNotSeeTableRecords(ImpersonationSession::whereNotNull('ended_at')->get())
            ->resetTableFilters()
            ->filterTable('admin_id', $this->other->id)
            ->assertCanSeeTableRecords([$open])
            ->assertCountTableRecords(1);
    }
}
