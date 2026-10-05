<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\User;
use App\Services\ServiceTemplateStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Unggah dan penyimpanan berkas template (admin API). */
class ServiceTemplateApiTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
        $this->service = Service::where('is_active', true)->firstOrFail();
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
    }

    /** A real temporary file: its type is detected from the content, unlike UploadedFile::fake(). */
    private function upload(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'tpl');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function pdf(string $name = 'Formulir.pdf', int $kb = 10): UploadedFile
    {
        return $this->upload($name, "%PDF-1.4\n" . str_repeat('x', $kb * 1024));
    }

    public function test_admin_uploads_a_template_stored_privately_per_service(): void
    {
        $response = $this->post("/api/layanan-ptsp/{$this->service->id}/template", [
            'berkas' => $this->pdf('Formulir Permohonan: v2.pdf'), 'nama' => 'Formulir permohonan', 'wajib' => true,
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.nama', 'Formulir permohonan')
            ->assertJsonPath('data.nama_berkas', 'Formulir Permohonan v2.pdf')
            ->assertJsonPath('data.wajib', true)
            ->assertJsonPath('data.tersedia', true);

        $template = ServiceTemplate::findOrFail($response->json('data.id'));
        $this->assertStringStartsWith("service-templates/{$this->service->id}/", $template->file_path);
        $this->assertStringEndsWith('.pdf', $template->file_path);
        $this->assertSame('application/pdf', $template->mime_type);
        Storage::disk('local')->assertExists($template->file_path);
    }

    public function test_files_are_checked_by_content_and_size(): void
    {
        $url = "/api/layanan-ptsp/{$this->service->id}/template";
        $headers = ['Accept' => 'application/json'];

        // A script renamed to .pdf is refused.
        $this->post($url, ['berkas' => $this->upload('formulir.pdf', '<?php echo 1;'), 'nama' => 'A'], $headers)
            ->assertStatus(422)->assertJsonPath('message', 'Template harus berupa PDF, Word, atau Excel.');
        $this->post($url, ['berkas' => $this->upload('gambar.png', "\x89PNG\r\n\x1a\n" . str_repeat('x', 100)), 'nama' => 'B'], $headers)->assertStatus(422);
        $this->post($url, ['berkas' => $this->pdf('besar.pdf', ServiceTemplateStorage::MAX_KB + 1), 'nama' => 'C'], $headers)
            ->assertStatus(422)->assertJsonValidationErrors('berkas');

        $this->assertSame(0, ServiceTemplate::count());
        $this->assertSame([], Storage::disk('local')->allFiles('service-templates'));
    }

    public function test_replacing_the_file_keeps_the_record_and_removes_the_old_file(): void
    {
        $id = $this->post("/api/layanan-ptsp/{$this->service->id}/template", ['berkas' => $this->pdf(), 'nama' => 'Formulir'], ['Accept' => 'application/json'])->json('data.id');
        $old = ServiceTemplate::findOrFail($id)->file_path;

        $this->post("/api/layanan-ptsp/{$this->service->id}/template/{$id}/berkas", ['berkas' => $this->pdf('Baru.pdf', 20)], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.nama_berkas', 'Baru.pdf')->assertJsonPath('data.ukuran', 20 * 1024 + 9);

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists(ServiceTemplate::findOrFail($id)->file_path);
    }

    public function test_names_are_unique_per_service_and_templates_belong_to_their_service(): void
    {
        $url = "/api/layanan-ptsp/{$this->service->id}/template";
        $id = $this->post($url, ['berkas' => $this->pdf(), 'nama' => 'Formulir'], ['Accept' => 'application/json'])->json('data.id');
        $this->post($url, ['berkas' => $this->pdf(), 'nama' => 'Formulir'], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors(['nama' => 'Layanan ini sudah punya template dengan nama yang sama.']);

        $other = Service::where('id', '!=', $this->service->id)->firstOrFail();
        $this->deleteJson("/api/layanan-ptsp/{$other->id}/template/{$id}")->assertNotFound();
        $this->deleteJson("{$url}/{$id}")->assertOk();
        $this->assertSame(0, ServiceTemplate::count());
        $this->assertSame([], Storage::disk('local')->allFiles('service-templates'));
    }

    public function test_only_admins_manage_templates(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->post("/api/layanan-ptsp/{$this->service->id}/template", ['berkas' => $this->pdf(), 'nama' => 'X'], ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_file_names_are_cleaned_for_download(): void
    {
        $this->assertSame('Surat Kuasa (ttd).docx', ServiceTemplateStorage::cleanName('Surat/Kuasa (ttd)?.DOCX'));
        $this->assertSame('template.pdf', ServiceTemplateStorage::cleanName('***.pdf'));
    }

    public function test_full_crud_with_version_and_active_status(): void
    {
        $url = "/api/layanan-ptsp/{$this->service->id}/template";
        $id = $this->post($url, ['berkas' => $this->pdf(), 'nama' => 'Formulir', 'urutan' => 2], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('data.versi', 1)->assertJsonPath('data.aktif', true)->json('data.id');
        $this->post($url, ['berkas' => $this->pdf('Lain.pdf'), 'nama' => 'Pernyataan', 'urutan' => 1], ['Accept' => 'application/json'])->assertCreated();

        $this->getJson($url)->assertOk()
            ->assertJsonPath('layanan.id', $this->service->id)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama', 'Pernyataan');

        $this->patchJson("{$url}/{$id}", ['nama' => 'Formulir permohonan', 'wajib' => true, 'aktif' => false])->assertOk()
            ->assertJsonPath('data.nama', 'Formulir permohonan')
            ->assertJsonPath('data.wajib', true)
            ->assertJsonPath('data.aktif', false)
            ->assertJsonPath('data.versi', 1);
        $this->patchJson("{$url}/{$id}", ['nama' => 'Pernyataan'])->assertStatus(422)->assertJsonValidationErrors('nama');

        // A new file raises the version; inactive templates stay listed for the admin.
        $this->post("{$url}/{$id}/berkas", ['berkas' => $this->pdf('v2.pdf')], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.versi', 2);
        $this->getJson("{$url}/{$id}")->assertOk()->assertJsonPath('data.aktif', false)->assertJsonPath('data.versi', 2);
        $this->getJson($url)->assertJsonCount(2, 'data');

        $this->deleteJson("{$url}/{$id}")->assertOk();
        $this->getJson("{$url}/{$id}")->assertNotFound();
    }

    public function test_public_list_shows_active_templates_only(): void
    {
        $url = "/api/layanan-ptsp/{$this->service->id}/template";
        $headers = ['Accept' => 'application/json'];
        $this->post($url, ['berkas' => $this->pdf('Formulir.pdf'), 'nama' => 'Formulir', 'wajib' => true, 'urutan' => 1], $headers)->assertCreated();
        $this->post($url, ['berkas' => $this->pdf('Kuasa.pdf'), 'nama' => 'Surat kuasa', 'urutan' => 2], $headers)->assertCreated();
        $hidden = $this->post($url, ['berkas' => $this->pdf('Lama.pdf'), 'nama' => 'Formulir lama', 'urutan' => 3], $headers)->json('data.id');
        $this->patchJson("{$url}/{$hidden}", ['aktif' => false])->assertOk();
        auth()->forgetGuards();

        $this->getJson("/api/publik/layanan/{$this->service->slug}/template")->assertOk()
            ->assertJsonPath('layanan.slug', $this->service->slug)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0', [
                'nama' => 'Formulir', 'wajib' => true, 'versi' => 1, 'jenis' => 'pdf', 'ukuran' => 10 * 1024 + 9, 'tersedia' => true,
                'unduh' => ServiceTemplate::where('nama', 'Formulir')->firstOrFail()->downloadUrl(),
            ])
            ->assertJsonPath('jumlah_wajib', 1)
            ->assertJsonPath('unduh_semua', route('onlineportal.service.templates.zip', $this->service->slug))
            ->assertJsonMissing(['nama' => 'Formulir lama']);
    }

    public function test_public_list_for_services_without_templates_or_inactive(): void
    {
        auth()->forgetGuards();
        $this->getJson("/api/publik/layanan/{$this->service->slug}/template")->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('unduh_semua', null)
            ->assertJsonPath('pesan', 'Layanan ini tidak memakai template berkas; cukup siapkan berkas persyaratan.');

        $this->service->update(['is_active' => false]);
        $this->getJson("/api/publik/layanan/{$this->service->slug}/template")->assertNotFound();
    }
}
