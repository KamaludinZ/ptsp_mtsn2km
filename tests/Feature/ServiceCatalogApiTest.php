<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/layanan-ptsp: the staff catalog. */
class ServiceCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_staff_list_every_service_with_counts_and_filters(): void
    {
        $inactive = Service::where('code', 'PCS-012')->firstOrFail();
        $inactive->update(['is_active' => false]);

        $this->getJson('/api/layanan-ptsp?per_halaman=100')->assertOk()
            ->assertJsonPath('meta.total', Service::count())
            ->assertJsonFragment(['kode' => 'PCS-012', 'aktif' => false]);

        $this->getJson('/api/layanan-ptsp?aktif=0')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.kode', 'PCS-012');
        $this->getJson('/api/layanan-ptsp?q=legalisir')->assertJsonPath('data.0.kode', 'LIT-002');
        $this->getJson('/api/layanan-ptsp?untuk=instansi&per_halaman=100')->assertOk()
            ->assertJsonFragment(['kode' => 'SPK-008'])->assertJsonMissing(['kode' => 'SKSA-001']);

        $sksa = collect($this->getJson('/api/layanan-ptsp?q=SKSA')->json('data'))->firstWhere('kode', 'SKSA-001');
        $this->assertSame(3, $sksa['jumlah_syarat']);
        $this->assertSame('Layanan Akademik', $sksa['kategori'][0]['nama']);
        $this->getJson('/api/layanan-ptsp?jalur=lain')->assertStatus(422);
    }

    public function test_detail_has_requirements_flow_templates_and_disposition(): void
    {
        $service = Service::where('code', 'SKSA-001')->firstOrFail();
        Ticket::factory()->create(['service_id' => $service->id, 'status' => 'in_process']);

        $this->getJson("/api/layanan-ptsp/{$service->id}")->assertOk()
            ->assertJsonPath('kode', 'SKSA-001')
            ->assertJsonPath('berkas_persyaratan.1.nama', 'Fotokopi Kartu Pelajar')
            ->assertJsonPath('disposisi.mode', 'tu')
            ->assertJsonPath('disposisi.unit_tujuan', ['tata_usaha'])
            ->assertJsonPath('disposisi.anjuran_tanda_tangan', 'tte')
            ->assertJsonPath('permohonan.berjalan', Ticket::where('service_id', $service->id)->open()->count())
            ->assertJsonPath('tautan_portal', route('onlineportal.service.detail', $service->slug))
            ->assertJsonPath('template_berkas', []);
    }

    public function test_applicants_use_the_public_catalog_instead(): void
    {
        Sanctum::actingAs(User::where('email', 'budi.santoso@email.com')->firstOrFail());

        $this->getJson('/api/layanan-ptsp')->assertForbidden();
        $this->getJson('/api/layanan-ptsp/' . Service::value('id'))->assertForbidden();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_admin_creates_a_service_with_everything_in_one_request(): void
    {
        Sanctum::actingAs($admin = $this->admin());
        $category = \App\Models\ServiceCategory::where('name', 'Layanan Umum')->firstOrFail();

        $response = $this->postJson('/api/layanan-ptsp', [
            'nama' => 'Surat Keterangan Lulus',
            'kode' => 'SKL-021',
            'kategori' => [$category->id],
            'jalur' => 'hybrid',
            'waktu_penyelesaian' => '1-2 hari kerja',
            'biaya' => 0,
            'persyaratan' => '<p>Ijazah</p><script>alert(1)</script>',
            'untuk' => ['alumni', 'alumni'],
            'disposisi' => ['mode' => 'tu', 'unit_tujuan' => ['tata_usaha'], 'anjuran_tanda_tangan' => 'tte'],
            'berkas_persyaratan' => [['nama' => 'Fotokopi ijazah'], ['nama' => 'KTP', 'wajib' => false]],
        ])->assertCreated()
            ->assertJsonPath('nama', 'Surat Keterangan Lulus')
            ->assertJsonPath('slug', 'surat-keterangan-lulus')
            ->assertJsonPath('kategori.0.nama', 'Layanan Umum')
            ->assertJsonPath('untuk', ['alumni'])
            ->assertJsonPath('disposisi.mode', 'tu')
            ->assertJsonPath('disposisi.unit_tujuan', ['tata_usaha'])
            ->assertJsonPath('berkas_persyaratan.1', ['nama' => 'KTP', 'wajib' => false, 'keterangan' => null]);

        $this->assertStringNotContainsString('<script', $response->json('persyaratan'));
        $service = Service::where('code', 'SKL-021')->firstOrFail();
        $this->assertSame($admin->id, $service->created_by);
        $this->assertDatabaseHas('activity_log', ['description' => 'Menambah layanan Surat Keterangan Lulus']);
    }

    public function test_admin_updates_only_the_fields_sent(): void
    {
        Sanctum::actingAs($this->admin());
        $service = Service::where('code', 'SKSA-001')->firstOrFail();
        $before = $service->only(['name', 'mode', 'processing_time']);
        $requirements = $service->requirements()->count();

        $this->patchJson("/api/layanan-ptsp/{$service->id}", ['aktif' => false, 'disposisi' => ['mode' => 'none']])->assertOk()
            ->assertJsonPath('aktif', false)
            ->assertJsonPath('disposisi.mode', 'none');

        $service->refresh();
        $this->assertSame($before, $service->only(['name', 'mode', 'processing_time']));
        $this->assertSame($requirements, $service->requirements()->count());
        $this->assertFalse($service->approval_required);
    }

    public function test_validation_messages_and_admin_only(): void
    {
        Sanctum::actingAs($this->admin());
        $this->postJson('/api/layanan-ptsp', ['nama' => 'X', 'kode' => 'SKSA-001', 'jalur' => 'pos', 'disposisi' => ['mode' => 'semua'],
            'berkas_persyaratan' => [['nama' => 'A'], ['nama' => 'A']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kode' => 'Kode layanan sudah dipakai layanan lain.', 'jalur', 'disposisi.mode', 'berkas_persyaratan.1.nama']);

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/layanan-ptsp', ['nama' => 'X', 'kode' => 'X-1', 'jalur' => 'online'])->assertForbidden();
        $this->patchJson('/api/layanan-ptsp/' . Service::value('id'), ['aktif' => false])->assertForbidden();
    }

    public function test_admin_switches_a_service_off_and_on(): void
    {
        Sanctum::actingAs($this->admin());
        $service = Service::where('code', 'SKSA-001')->firstOrFail();
        Ticket::factory()->create(['service_id' => $service->id, 'status' => 'in_process']);
        $open = Ticket::where('service_id', $service->id)->open()->count();

        $this->patchJson("/api/layanan-ptsp/{$service->id}/aktif", ['aktif' => false])->assertOk()
            ->assertJsonPath('aktif', false)
            ->assertJsonPath('berubah', true)
            ->assertJsonPath('permohonan_berjalan', $open)
            ->assertJsonPath('message', "Layanan dinonaktifkan dan tidak bisa diajukan lagi. {$open} permohonan berjalan tetap diproses.");
        $this->assertFalse($service->fresh()->is_active);
        $this->assertDatabaseHas('activity_log', ['description' => 'Menonaktifkan layanan ' . $service->name]);
        $this->get(route('onlineportal.service.detail', $service->slug))->assertNotFound();

        $this->patchJson("/api/layanan-ptsp/{$service->id}/aktif", ['aktif' => false])->assertOk()->assertJsonPath('berubah', false);
        $this->patchJson("/api/layanan-ptsp/{$service->id}/aktif", ['aktif' => true])->assertOk()
            ->assertJsonPath('message', 'Layanan diaktifkan dan tampil kembali di portal.');
        $this->assertTrue($service->fresh()->is_active);

        $this->patchJson("/api/layanan-ptsp/{$service->id}/aktif", [])->assertStatus(422);
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->patchJson("/api/layanan-ptsp/{$service->id}/aktif", ['aktif' => false])->assertForbidden();
    }
}
