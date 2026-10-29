<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\Persuratan;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/penomoran-sk: the SK register with its filters. */
class RegisterSkApiTest extends TestCase
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

    private function sk(int $tahun, int $urut, array $data = []): SuratKeputusan
    {
        return SuratKeputusan::create([
            'tahun' => $tahun,
            'nomor_urut' => $urut,
            'nomor_sk' => SuratKeputusan::composeNumber($urut, $tahun),
            'tanggal_surat' => "{$tahun}-03-01",
            'perihal' => 'SK uji ' . $urut,
            'jenis_sk' => 'Keuangan dan Operasional',
            'nama_peminta' => 'Peminta Uji',
            ...$data,
        ]);
    }

    public function test_back_office_reads_this_years_register_newest_first(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->sk(now()->year - 1, 300);
        $year = now()->year;

        $this->getJson('/api/penomoran-sk')->assertOk()
            ->assertJsonPath('total', 6)
            ->assertJsonPath('data.0.nomor_urut', 6)
            ->assertJsonPath('data.0.nomor_sk', "6 TAHUN {$year}")
            ->assertJsonPath('data.5.nomor_sk', "1 TAHUN {$year}")
            ->assertJsonPath('data.5.jenis_sk', Persuratan::JENIS_SK[0])
            ->assertJsonPath('data.5.status', 'menunggu_scan')
            ->assertJsonPath('data.5.status_label', 'Menunggu scan')
            ->assertJsonPath('data.5.ada_scan', false)
            ->assertJsonStructure(['data' => [['id', 'nomor_urut', 'nomor_sk', 'tahun', 'tanggal_surat', 'perihal', 'jenis_sk', 'nama_peminta', 'keterangan', 'status', 'petugas', 'dicatat']], 'halaman', 'total', 'halaman_terakhir'])
            ->assertJsonMissingPath('data.0.berkas_scan');

        $this->getJson('/api/penomoran-sk?tahun=' . ($year - 1))->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_sk', '300 TAHUN ' . ($year - 1));
    }

    public function test_filters_by_jenis_status_search_and_date(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $year = now()->year;
        $this->sk($year, 50, ['berkas_scan' => 'surat-keputusan/50.pdf', 'perihal' => 'Tim adiwiyata', 'tanggal_surat' => "{$year}-11-20"]);

        $this->getJson('/api/penomoran-sk?jenis_sk=' . urlencode('Kepanitiaan dan Tim Pelaksana Kegiatan'))->assertOk()->assertJsonPath('total', 2);
        $this->getJson('/api/penomoran-sk?' . http_build_query(['jenis_sk' => ['Kepanitiaan dan Tim Pelaksana Kegiatan', 'Keuangan dan Operasional']]))->assertOk()->assertJsonPath('total', 4);
        $this->getJson('/api/penomoran-sk?status=terarsip')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.ada_scan', true);
        $this->getJson('/api/penomoran-sk?status=menunggu_scan')->assertOk()->assertJsonPath('total', 6);
        $this->getJson('/api/penomoran-sk?q=adiwiyata')->assertOk()->assertJsonPath('total', 1);
        $this->getJson('/api/penomoran-sk?q=50')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_urut', 50);
        $this->getJson('/api/penomoran-sk?q=' . urlencode('kesiswaan'))->assertOk()->assertJsonPath('total', 2); // nama peminta
        $this->getJson('/api/penomoran-sk?nomor_urut=3')->assertOk()->assertJsonPath('total', 1);
        $this->getJson("/api/penomoran-sk?dari={$year}-11-01&sampai={$year}-11-30")->assertOk()->assertJsonPath('total', 1);
    }

    public function test_sorts_and_paginates(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson('/api/penomoran-sk?arah=asc')->assertOk()->assertJsonPath('data.0.nomor_urut', 1);
        $this->getJson('/api/penomoran-sk?urutkan=tanggal_surat&arah=asc')->assertOk()->assertJsonPath('data.0.nomor_urut', 1);

        $page = $this->getJson('/api/penomoran-sk?per_halaman=4&page=2')->assertOk();
        $this->assertCount(2, $page->json('data'));
        $this->assertSame(2, $page->json('halaman'));
        $this->assertSame(2, $page->json('halaman_terakhir'));
    }

    public function test_search_also_finds_by_jenis_and_ignores_a_leading_tentang(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson('/api/penomoran-sk?q=' . urlencode('keuangan dan'))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_urut', 5);
        $this->getJson('/api/penomoran-sk?q=kepanitiaan')->assertOk()->assertJsonPath('total', 2);
        $this->getJson('/api/penomoran-sk?q=' . urlencode('Tentang panitia penerimaan'))->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_urut', 3);
    }

    public function test_filters_by_officer_and_reads_every_year(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $maker = User::role('back_office')->first();
        $admin = $this->user('ptsp@mtsn2malang.sch.id');
        $year = now()->year;
        $this->sk($year, 7, ['pembuat_id' => $admin->id]);
        $this->sk($year - 1, 300, ['pembuat_id' => $admin->id]);

        $this->getJson("/api/penomoran-sk?petugas={$admin->id}")->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_urut', 7);
        $this->getJson("/api/penomoran-sk?petugas={$maker->id}")->assertOk()->assertJsonPath('total', 6);

        $all = $this->getJson('/api/penomoran-sk?tahun=semua')->assertOk()->assertJsonPath('tahun', 'semua')->assertJsonPath('total', 8);
        $this->assertSame("7 TAHUN {$year}", $all->json('data.0.nomor_sk'));
        $this->assertSame('300 TAHUN ' . ($year - 1), $all->json('data.7.nomor_sk'));
        $this->getJson('/api/penomoran-sk?tahun=semua&arah=asc')->assertOk()->assertJsonPath('data.0.nomor_sk', '300 TAHUN ' . ($year - 1));
        $this->getJson("/api/penomoran-sk?tahun=semua&petugas={$admin->id}")->assertOk()->assertJsonPath('total', 2);
    }

    public function test_each_sk_tells_who_recorded_it_in_which_role_and_its_scan(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        SuratKeputusan::where('nomor_urut', 1)->update(['berkas_scan' => 'surat-keputusan/1.pdf', 'berkas_scan_nama' => 'SK wali kelas.pdf', 'scan_diunggah_pada' => now()]);

        $this->getJson('/api/penomoran-sk?arah=asc')->assertOk()
            ->assertJsonPath('tahun', now()->year)
            ->assertJsonPath('per_halaman', 25)
            ->assertJsonPath('data.0.nama_scan', 'SK wali kelas.pdf')
            ->assertJsonPath('data.0.peran_petugas', ['nama' => 'back_office', 'label' => RoleAccess::roleLabel('back_office')])
            ->assertJsonPath('data.1.nama_scan', null)
            ->assertJsonStructure(['data' => [['diubah']]]);
    }

    public function test_invalid_filters_are_refused(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $this->getJson('/api/penomoran-sk?tahun=1999')->assertJsonValidationErrors('tahun');
        $this->getJson('/api/penomoran-sk?status=hilang')->assertJsonValidationErrors('status');
        $this->getJson('/api/penomoran-sk?nomor_urut=10000')->assertJsonValidationErrors('nomor_urut');
        $this->getJson('/api/penomoran-sk?dari=2026-05-01&sampai=2026-04-01')->assertJsonValidationErrors('sampai');
        $this->getJson('/api/penomoran-sk?per_halaman=500')->assertJsonValidationErrors('per_halaman');
        $this->getJson('/api/penomoran-sk?urutkan=perihal')->assertJsonValidationErrors('urutkan');
        $this->getJson('/api/penomoran-sk?tahun=semuanya')->assertJsonValidationErrors('tahun');
        $this->getJson('/api/penomoran-sk?petugas=999999')->assertJsonValidationErrors('petugas');
    }

    public function test_filter_choices_follow_the_register_and_master(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->sk(2024, 1, ['jenis_sk' => 'Jenis Lama']);
        PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'Kurikulum dan Akademik', 'sort' => 9]);

        $this->getJson('/api/penomoran-sk/saringan')->assertOk()
            ->assertJsonPath('tahun', [now()->year, 2024])
            ->assertJsonPath('jenis_sk', [...Persuratan::JENIS_SK, 'Kurikulum dan Akademik', 'Jenis Lama'])
            ->assertJsonPath('status.0', ['nilai' => 'menunggu_scan', 'label' => 'Menunggu scan'])
            ->assertJsonPath('status.1.nilai', 'terarsip')
            ->assertJsonPath('petugas', [['id' => User::role('back_office')->first()->id, 'nama' => User::role('back_office')->first()->name]]);
    }

    public function test_front_desk_and_guests_have_no_access(): void
    {
        $this->getJson('/api/penomoran-sk')->assertUnauthorized();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);
        $this->getJson('/api/penomoran-sk')->assertForbidden();
        $this->getJson('/api/penomoran-sk/saringan')->assertForbidden();
    }

    private function payload(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Pembentukan Tim Khusus dan Regulasi Internal',
            'perihal' => '  Tim   penyusun tata tertib madrasah ',
            'nama_peminta' => 'Waka Kesiswaan',
            'keterangan' => 'Rapat 3 kali.',
            ...$override,
        ];
    }

    public function test_back_office_asks_for_the_next_sk_number(): void
    {
        Sanctum::actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $year = now()->year;

        $this->postJson('/api/penomoran-sk', $this->payload())->assertCreated()
            ->assertJsonPath('nomor_urut', 7)
            ->assertJsonPath('nomor_sk', "7 TAHUN {$year}")
            ->assertJsonPath('perihal', 'Tim penyusun tata tertib madrasah')
            ->assertJsonPath('jenis_sk', 'Pembentukan Tim Khusus dan Regulasi Internal')
            ->assertJsonPath('petugas', $officer->name)
            ->assertJsonPath('status', 'menunggu_scan');

        // Another year starts at 1, apart from this year's sequence.
        $this->postJson('/api/penomoran-sk', $this->payload(['tanggal_surat' => ($year + 1) . '-01-05']))->assertCreated()
            ->assertJsonPath('nomor_sk', '1 TAHUN ' . ($year + 1));
        $this->postJson('/api/penomoran-sk', $this->payload())->assertCreated()->assertJsonPath('nomor_urut', 8);
    }

    public function test_asking_for_a_number_needs_the_sk_data_and_a_listed_jenis(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $before = SuratKeputusan::count();
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        $this->postJson('/api/penomoran-sk', [])->assertJsonValidationErrors(['tanggal_surat', 'jenis_sk', 'perihal', 'nama_peminta']);
        $this->postJson('/api/penomoran-sk', $this->payload(['jenis_sk' => 'Diketik Sendiri']))->assertJsonValidationErrors(['jenis_sk' => 'Master Persuratan']);
        $this->postJson('/api/penomoran-sk', $this->payload(['jenis_sk' => 'Keuangan dan Operasional']))->assertJsonValidationErrors('jenis_sk');
        $this->postJson('/api/penomoran-sk', $this->payload(['perihal' => '   ']))->assertJsonValidationErrors('perihal');
        $this->postJson('/api/penomoran-sk', $this->payload(['tanggal_surat' => 'kemarin']))->assertJsonValidationErrors('tanggal_surat');

        $this->assertSame($before, SuratKeputusan::count());
    }

    public function test_the_signed_scan_may_come_with_the_request(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);

        $response = $this->post('/api/penomoran-sk', [...$this->payload(), 'berkas_scan' => UploadedFile::fake()->create('SK Tim Tatib.pdf', 300, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('nomor_urut', 7)
            ->assertJsonPath('status', 'terarsip')
            ->assertJsonPath('nama_scan', 'SK Tim Tatib.pdf')
            ->assertJsonPath('peran_petugas.nama', 'back_office')
            ->assertJsonMissingPath('berkas_scan');

        $sk = SuratKeputusan::findOrFail($response->json('id'));
        $this->assertStringStartsWith(SuratKeputusan::SCAN_DIRECTORY . '/', $sk->berkas_scan);
        Storage::disk('local')->assertExists($sk->berkas_scan);
    }

    public function test_a_wrong_scan_or_date_gives_no_number_and_leaves_no_file(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $before = SuratKeputusan::count();
        $post = fn (array $data) => $this->post('/api/penomoran-sk', [...$this->payload(), ...$data], ['Accept' => 'application/json']);

        $post(['berkas_scan' => UploadedFile::fake()->create('sk.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])
            ->assertJsonValidationErrors(['berkas_scan' => 'PDF, JPG, atau PNG']);
        $post(['berkas_scan' => UploadedFile::fake()->create('besar.pdf', SuratKeputusan::SCAN_MAX_SIZE + 1, 'application/pdf')])
            ->assertJsonValidationErrors(['berkas_scan' => '10 MB']);
        $post(['berkas_scan' => 'surat-keputusan/../.env'])->assertJsonValidationErrors('berkas_scan');
        $post(['tanggal_surat' => '1999-12-31'])->assertJsonValidationErrors(['tanggal_surat' => '2000']);
        $post(['tanggal_surat' => '2101-01-01'])->assertJsonValidationErrors(['tanggal_surat' => '2100']);

        $this->assertSame($before, SuratKeputusan::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_full_year_refuses_the_request_and_drops_its_scan(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->sk(now()->year, 9999);

        $this->post('/api/penomoran-sk', [...$this->payload(), 'berkas_scan' => UploadedFile::fake()->create('SK.pdf', 100, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Nomor SK tahun ' . now()->year . ' sudah mencapai 9999.');

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_numbers_stop_at_9999_per_year(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->sk(now()->year, 9999);

        $this->postJson('/api/penomoran-sk', $this->payload())->assertStatus(422)
            ->assertJsonPath('message', 'Nomor SK tahun ' . now()->year . ' sudah mencapai 9999.');
    }

    public function test_back_office_corrects_an_sk_but_keeps_its_number_and_year(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = SuratKeputusan::where('nomor_urut', 5)->firstOrFail();
        $year = now()->year;

        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Penetapan bendahara (revisi)', 'nomor_sk' => '1 TAHUN 2000', 'nomor_urut' => 1])->assertOk()
            ->assertJsonPath('perihal', 'Penetapan bendahara (revisi)')
            ->assertJsonPath('nomor_sk', "5 TAHUN {$year}")
            ->assertJsonPath('nama_peminta', $sk->nama_peminta);

        $this->putJson("/api/penomoran-sk/{$sk->id}", ['tanggal_surat' => ($year - 1) . '-12-31'])->assertStatus(422)
            ->assertJsonPath('message', "Tanggal surat harus dalam tahun {$year}, tahun nomor SK ini.");
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['tanggal_surat' => "{$year}-12-31"])->assertOk()->assertJsonPath('tanggal_surat', "{$year}-12-31");
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => ''])->assertJsonValidationErrors('perihal');
    }

    public function test_an_sk_keeps_its_jenis_after_the_jenis_is_switched_off(): void
    {
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $sk = SuratKeputusan::where('jenis_sk', 'Keuangan dan Operasional')->firstOrFail();
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        $this->putJson("/api/penomoran-sk/{$sk->id}", ['jenis_sk' => 'Keuangan dan Operasional', 'keterangan' => 'Tetap'])->assertOk();
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['jenis_sk' => 'Diketik Sendiri'])->assertJsonValidationErrors('jenis_sk');
        $this->assertSame('Keuangan dan Operasional', $sk->fresh()->jenis_sk);
    }

    public function test_only_administrators_delete_and_must_give_a_reason(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 2)->firstOrFail();

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'), ['*']);
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Dibatalkan pimpinan.'])->assertForbidden();

        Sanctum::actingAs($admin = $this->user('ptsp@mtsn2malang.sch.id'), ['*']);
        $this->deleteJson("/api/penomoran-sk/{$sk->id}")->assertJsonValidationErrors(['alasan' => 'Log & Audit']);
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Dibatalkan pimpinan.'])->assertNoContent();

        $this->assertModelMissing($sk);
        $log = \Spatie\Activitylog\Models\Activity::inLog('surat_keputusan')->latest('id')->firstOrFail();
        $this->assertSame('Dibatalkan pimpinan.', $log->properties['alasan']);
        $this->assertSame($admin->id, $log->causer_id);
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Lagi.'])->assertNotFound();
    }

    public function test_front_desk_cannot_change_the_register(): void
    {
        $sk = SuratKeputusan::firstOrFail();
        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'), ['*']);

        $this->postJson('/api/penomoran-sk', $this->payload())->assertForbidden();
        $this->putJson("/api/penomoran-sk/{$sk->id}", ['perihal' => 'Diubah loket'])->assertForbidden();
        $this->deleteJson("/api/penomoran-sk/{$sk->id}", ['alasan' => 'Dihapus loket'])->assertForbidden();
        $this->assertSame($sk->perihal, $sk->fresh()->perihal);
    }
}
