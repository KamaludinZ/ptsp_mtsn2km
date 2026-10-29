<?php

namespace Tests\Feature;

use App\Filament\Pages\System\ActivityTrail;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Master Jenis SK in Log & Audit: who added, changed or deleted a jenis, and nothing for the other lists. */
class JenisSkAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    private function trail()
    {
        return Activity::inLog('jenis_sk')->orderBy('id')->get();
    }

    public function test_the_defaults_are_not_logged_as_an_officer_s_change(): void
    {
        $this->assertCount(0, $this->trail());
    }

    public function test_adding_changing_and_deleting_a_jenis_is_logged_with_who_and_role(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $id = $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'SK Penghargaan'])->assertCreated()->json('id');
        $this->putJson("/api/persuratan/jenis-sk/{$id}", ['nama' => 'Penghargaan dan Apresiasi', 'aktif' => false])->assertOk();
        Sanctum::actingAs($this->admin, ['*']);
        $this->deleteJson("/api/persuratan/jenis-sk/{$id}")->assertNoContent();

        [$created, $updated, $deleted] = $this->trail()->all();
        $this->assertSame(['Menambah jenis SK SK Penghargaan', 'created'], [$created->description, $created->event]);
        $this->assertTrue($created->causer->is($this->officer));
        $this->assertSame('back_office', $created->properties['peran_aktif']);

        $this->assertSame('Mengubah jenis SK Penghargaan dan Apresiasi', $updated->description);
        $this->assertSame(['nama' => 'Penghargaan dan Apresiasi', 'is_active' => false], $updated->properties['attributes']);
        $this->assertSame(['nama' => 'SK Penghargaan', 'is_active' => true], $updated->properties['old']);

        $this->assertSame('Menghapus jenis SK Penghargaan dan Apresiasi', $deleted->description);
        $this->assertTrue($deleted->causer->is($this->admin));
    }

    public function test_the_other_master_lists_are_not_logged(): void
    {
        $this->actingAs($this->officer);
        PersuratanMaster::create(['type' => 'tujuan_naskah', 'nama' => 'Kepala Dinas Sosial', 'is_active' => true, 'sort' => 9])
            ->update(['is_active' => false]);

        $this->assertCount(0, $this->trail());
    }

    public function test_rekam_jejak_shows_master_jenis_sk_changes(): void
    {
        [$kepegawaian] = Persuratan::JENIS_SK;
        $this->actingAs($this->officer);
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', $kepegawaian)->firstOrFail()
            ->update(['nama' => 'Kepegawaian, Pengangkatan, dan Mutasi']);

        $this->actingAs($this->admin);
        Livewire::test(ActivityTrail::class)
            ->call('setType', 'register_sk')
            ->assertSee('Mengubah jenis SK Kepegawaian, Pengangkatan, dan Mutasi')
            ->call('showDetail', $this->trail()->sole()->id)
            ->assertSee("{$kepegawaian} → Kepegawaian, Pengangkatan, dan Mutasi");
    }
}
