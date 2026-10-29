<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** /api/persuratan/jenis-sk: managing the Jenis SK list over the API. */
class JenisSkApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function jenis(string $nama): PersuratanMaster
    {
        return PersuratanMaster::where('type', 'jenis_sk')->where('nama', $nama)->firstOrFail();
    }

    public function test_back_office_lists_only_jenis_sk_in_order_and_can_filter(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->jenis('Keuangan dan Operasional')->update(['is_active' => false]);

        $this->getJson('/api/persuratan/jenis-sk')->assertOk()
            ->assertJsonPath('total', 5)
            ->assertJsonPath('data.0.nama', Persuratan::JENIS_SK[0])
            ->assertJsonPath('data.4.aktif', false)
            ->assertJsonStructure(['data' => [['id', 'nama', 'aktif', 'urutan']]]);

        $this->getJson('/api/persuratan/jenis-sk?aktif=1')->assertOk()->assertJsonPath('total', 4);
        $this->getJson('/api/persuratan/jenis-sk?q=panitia')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.nama', 'Kepanitiaan dan Tim Pelaksana Kegiatan');
        $this->getJson('/api/persuratan/jenis-sk?q=100%25')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_back_office_adds_a_jenis_sk_at_the_end_of_the_list(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->postJson('/api/persuratan/jenis-sk', ['nama' => '  Kurikulum   dan Akademik '])
            ->assertCreated()
            ->assertJsonPath('nama', 'Kurikulum dan Akademik')
            ->assertJsonPath('aktif', true)
            ->assertJsonPath('urutan', 5);

        $this->assertSame([...Persuratan::JENIS_SK, 'Kurikulum dan Akademik'], Persuratan::options('jenis_sk'));
        $this->assertDatabaseHas('persuratan_masters', ['type' => 'jenis_sk', 'nama' => 'Kurikulum dan Akademik', 'kode' => null]);
    }

    public function test_names_are_validated_and_duplicates_refused_ignoring_case(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        PersuratanMaster::create(['type' => 'tujuan_naskah', 'nama' => 'Tim Adiwiyata']);

        $this->postJson('/api/persuratan/jenis-sk', [])->assertJsonValidationErrors(['nama' => 'wajib']);
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'AB'])->assertJsonValidationErrors('nama');
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => ['x']])->assertJsonValidationErrors('nama');
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => ' keuangan  DAN operasional '])->assertJsonValidationErrors(['nama' => 'sudah ada']);
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Baru', 'urutan' => 10000])->assertJsonValidationErrors('urutan');
        // The same name in another Master Persuratan list is not a duplicate.
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Tim Adiwiyata'])->assertCreated();
    }

    public function test_back_office_reads_and_updates_one_jenis_sk(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $jenis = $this->jenis('Keuangan dan Operasional');

        $this->getJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertOk()->assertJsonPath('nama', 'Keuangan dan Operasional');

        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'Keuangan, Sarana, dan Operasional', 'aktif' => false, 'urutan' => 0])
            ->assertOk()
            ->assertJsonPath('nama', 'Keuangan, Sarana, dan Operasional')
            ->assertJsonPath('aktif', false)
            ->assertJsonPath('urutan', 0);
        // Keeping its own name is not a duplicate; taking another one is.
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'keuangan, sarana, dan operasional'])->assertOk();
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'Kepegawaian dan Pengangkatan'])->assertJsonValidationErrors('nama');
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['aktif' => true])->assertOk()->assertJsonPath('nama', 'keuangan, sarana, dan operasional');

        $this->assertSame('jenis_sk', $jenis->fresh()->type);
    }

    public function test_each_jenis_tells_how_many_sk_use_it(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $jenis = $this->jenis('Keuangan dan Operasional');
        $year = now()->year - 1;
        SuratKeputusan::create(['tahun' => $year, 'nomor_urut' => 9, 'nomor_sk' => SuratKeputusan::composeNumber(9, $year), 'tanggal_surat' => "{$year}-02-01",
            'perihal' => 'Penetapan bendahara', 'jenis_sk' => 'Keuangan dan Operasional', 'nama_peminta' => 'Kepala Tata Usaha']);

        $this->getJson('/api/persuratan/jenis-sk')->assertOk()
            ->assertJsonPath('data.0.jumlah_sk', 1)
            ->assertJsonPath('data.2.jumlah_sk', 2)
            ->assertJsonPath('data.4.jumlah_sk', 2);
        $this->getJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertOk()->assertJsonPath('jumlah_sk', 2);
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Kurikulum dan Akademik'])->assertCreated()->assertJsonPath('jumlah_sk', 0);

        // Recorded SK keep the name they were saved with, and still count for the renamed jenis (jenis_sk_id).
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'Keuangan dan Sarana'])->assertOk()->assertJsonPath('jumlah_sk', 2);
        $this->assertSame(2, SuratKeputusan::where('jenis_sk', 'Keuangan dan Operasional')->count());
    }

    public function test_a_name_stored_by_someone_else_a_moment_earlier_is_refused_cleanly(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        // Another officer stores the same name between this request's check and its save.
        PersuratanMaster::creating(function (PersuratanMaster $jenis) {
            if ($jenis->nama === 'Kurikulum dan Akademik') {
                DB::table('persuratan_masters')->insert(['type' => 'jenis_sk', 'nama' => 'KURIKULUM DAN AKADEMIK', 'created_at' => now(), 'updated_at' => now()]);
            }
        });
        $jenis = $this->jenis('Keuangan dan Operasional');
        PersuratanMaster::updating(function (PersuratanMaster $row) {
            if ($row->nama === 'Tim Adiwiyata') {
                DB::table('persuratan_masters')->insert(['type' => 'jenis_sk', 'nama' => 'tim adiwiyata', 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Kurikulum dan Akademik'])->assertStatus(422)->assertJsonValidationErrors(['nama' => 'sudah ada']);
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'Tim Adiwiyata'])->assertStatus(422)->assertJsonValidationErrors(['nama' => 'sudah ada']);

        $this->assertSame('Keuangan dan Operasional', $jenis->fresh()->nama);
        $this->assertFalse(PersuratanMaster::where('type', 'jenis_sk')->whereRaw('lower(nama) in (?, ?)', ['kurikulum dan akademik', 'tim adiwiyata'])->exists());
        // The connection is still usable after the refused save.
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Kesiswaan dan Kegiatan'])->assertCreated();
    }

    public function test_only_administrators_delete_a_jenis_sk(): void
    {
        $jenis = $this->jenis('Keuangan dan Operasional');

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->deleteJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertForbidden();

        Sanctum::actingAs($this->user('katu@mtsn2malang.sch.id'), ['*']);
        $this->deleteJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertForbidden();

        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $this->deleteJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertNoContent();
        $this->assertDatabaseMissing('persuratan_masters', ['id' => $jenis->id]);
    }

    public function test_front_desk_and_guests_have_no_access(): void
    {
        $jenis = $this->jenis('Keuangan dan Operasional');

        $this->getJson('/api/persuratan/jenis-sk')->assertUnauthorized();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);
        $this->getJson('/api/persuratan/jenis-sk')->assertForbidden();
        $this->getJson("/api/persuratan/jenis-sk/{$jenis->id}")->assertForbidden();
        $this->postJson('/api/persuratan/jenis-sk', ['nama' => 'Kesiswaan'])->assertForbidden();
        $this->putJson("/api/persuratan/jenis-sk/{$jenis->id}", ['nama' => 'Kesiswaan'])->assertForbidden();
    }

    public function test_other_master_lists_are_not_reachable_here(): void
    {
        $tembusan = PersuratanMaster::where('type', 'tembusan')->firstOrFail();

        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $this->getJson("/api/persuratan/jenis-sk/{$tembusan->id}")->assertNotFound();
        $this->putJson("/api/persuratan/jenis-sk/{$tembusan->id}", ['nama' => 'Diubah'])->assertNotFound();
        $this->deleteJson("/api/persuratan/jenis-sk/{$tembusan->id}")->assertNotFound();

        $this->assertDatabaseHas('persuratan_masters', ['id' => $tembusan->id, 'nama' => $tembusan->nama]);
    }
}
