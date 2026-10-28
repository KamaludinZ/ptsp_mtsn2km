<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The guest book APIs register both kinds of guest, as the counter's form and the public form do. */
class VisitorRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function service(): Service
    {
        return Service::where('is_active', true)->orderBy('name')->firstOrFail();
    }

    private function counter(): User
    {
        return User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_the_counter_api_registers_an_applicant(): void
    {
        Sanctum::actingAs($this->counter());

        $this->postJson('/api/buku-tamu', ['jenis' => 'pemohon', 'name' => 'Budi', 'phone' => '081200000010', 'applicant_type' => 'wali_murid',
            'target_service' => $this->service()->name, 'notes' => 'Membawa berkas', 'obscure_name' => true])
            ->assertCreated()
            ->assertJsonPath('jenis', 'pemohon')
            ->assertJsonPath('jenis_label', 'Pemohon Layanan Offline')
            ->assertJsonPath('status_pemohon', 'wali_murid')
            ->assertJsonPath('layanan', $this->service()->name)
            ->assertJsonPath('keperluan', 'Pemohon Layanan: ' . $this->service()->name);

        $visitor = Visitor::where('name', 'Budi')->firstOrFail();
        $this->assertSame([$this->counter()->id, true], [$visitor->created_by, (bool) $visitor->is_obscured]);

        $this->postJson('/api/buku-tamu', ['jenis' => 'pemohon', 'name' => 'X', 'phone' => '', 'target_service' => 'Lainnya'])
            ->assertJsonValidationErrors([
                'phone' => 'Nomor telepon/HP wajib diisi.',
                'target_service_other' => 'Tuliskan layanan yang Anda tuju.',
            ]);
        $this->postJson('/api/buku-tamu', ['jenis' => 'tamu', 'name' => 'X'])->assertJsonValidationErrors('jenis');
    }

    public function test_the_counter_api_keeps_registering_visiting_guests_and_takes_the_form_s_fields(): void
    {
        Sanctum::actingAs($this->counter());

        // As before: a free-text purpose; whom they meet is optional now, as in the form.
        $this->postJson('/api/buku-tamu', ['name' => 'Tamu Dinas', 'purpose' => 'Monitoring'])->assertCreated()
            ->assertJsonPath('jenis', 'kunjungan')
            ->assertJsonPath('bertemu', null);

        $this->postJson('/api/buku-tamu', ['jenis' => 'kunjungan', 'name' => 'Rina', 'phone' => '081200000011', 'institution_category' => 'pendidikan',
            'purpose' => 'Lainnya', 'purpose_other' => 'Studi banding', 'notes' => 'Rombongan', 'obscure_name' => true])
            ->assertCreated()
            ->assertJsonPath('keperluan', 'Studi banding');
        $this->assertSame(['pendidikan', 'Rombongan', true], [Visitor::where('name', 'Rina')->value('institution_category'), Visitor::where('name', 'Rina')->value('notes'), (bool) Visitor::where('name', 'Rina')->value('is_obscured')]);

        $this->postJson('/api/buku-tamu', ['name' => 'X', 'purpose' => 'Lainnya', 'institution_category' => 'koperasi'])
            ->assertJsonValidationErrors(['purpose_other', 'institution_category']);

        $listed = collect($this->getJson('/api/buku-tamu?tanggal=' . today()->toDateString())->assertOk()->json('data'))->keyBy('nama');
        $this->assertSame('kunjungan', $listed['Rina']['jenis']);
    }

    public function test_the_public_api_takes_the_applicant_form_too(): void
    {
        $this->postJson('/api/publik/buku-tamu', ['jenis' => 'pemohon', 'name' => 'Sari', 'phone' => '081200000012', 'applicant_type' => 'instansi_perusahaan',
            'institution' => 'PT Maju', 'target_service' => 'Lainnya', 'target_service_other' => 'Kerja sama PKL', 'obscure_name' => true])
            ->assertCreated()
            ->assertJsonPath('ringkasan.3.nilai', 'Pemohon Layanan: Kerja sama PKL');

        $visitor = Visitor::where('name', 'Sari')->firstOrFail();
        $this->assertSame(['pemohon', 'instansi_perusahaan', null, null], [$visitor->guest_type, $visitor->applicant_type, $visitor->service_id, $visitor->created_by]);

        $this->postJson('/api/publik/buku-tamu', ['jenis' => 'pemohon', 'name' => 'X', 'phone' => '0812', 'target_service' => 'Layanan karangan'])
            ->assertJsonValidationErrors(['target_service' => 'Pilih layanan dari daftar yang tersedia.']);

        // Without jenis it is the visiting guest's form, as before.
        $this->postJson('/api/publik/buku-tamu', ['name' => 'Pak Budi', 'phone' => '0813', 'purpose' => 'Komite'])->assertCreated();
        $this->assertSame('kunjungan', Visitor::where('name', 'Pak Budi')->value('guest_type'));
    }

    public function test_the_options_name_every_choice_of_both_forms(): void
    {
        $this->getJson('/api/publik/buku-tamu/pilihan')->assertOk()
            ->assertJsonPath('jenis_tamu.1', ['nilai' => 'pemohon', 'label' => 'Pemohon Layanan Offline'])
            ->assertJsonPath('status_pemohon.5.nilai', 'instansi_perusahaan')
            ->assertJsonFragment(['kategori_instansi' => Visitor::institutionCategoryKeys()])
            ->assertJsonPath('layanan.0', $this->service()->name)
            ->assertJsonPath('layanan', fn (array $names) => end($names) === 'Lainnya');
    }
}
