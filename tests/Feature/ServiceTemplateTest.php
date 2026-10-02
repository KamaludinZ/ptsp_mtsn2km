<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\RelationManagers\TemplatesRelationManager;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Template berkas layanan: uploaded by the admin, downloaded by applicants. */
class ServiceTemplateTest extends TestCase
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

        $this->service = Service::availableFor('umum')->firstOrFail();
    }

    private function template(array $attributes = []): ServiceTemplate
    {
        Storage::disk('local')->put('service-templates/formulir.docx', 'isi template');

        return $this->service->templates()->create([
            'nama' => 'Formulir permohonan',
            'file_path' => 'service-templates/formulir.docx',
            'file_name' => 'formulir-permohonan.docx',
            'is_required' => true,
            ...$attributes,
        ]);
    }

    public function test_service_page_lists_templates_for_download(): void
    {
        $template = $this->template();

        $this->get(route('onlineportal.service.detail', $this->service->slug))
            ->assertOk()
            ->assertSee('Template Berkas')
            ->assertSee('Formulir permohonan')
            ->assertSee('Wajib')
            ->assertSee($template->downloadUrl(), false);
    }

    public function test_anyone_can_download_a_template_of_an_active_service(): void
    {
        $template = $this->template();

        $this->get($template->downloadUrl())
            ->assertOk()
            ->assertDownload('formulir-permohonan.docx');
    }

    public function test_template_must_belong_to_the_service_in_the_address(): void
    {
        $template = $this->template();
        $other = Service::where('is_active', true)->whereKeyNot($this->service->id)->firstOrFail();

        $this->get(route('onlineportal.service.template', [$other->slug, $template]))->assertNotFound();
    }

    public function test_service_page_without_templates_hides_the_section(): void
    {
        $this->get(route('onlineportal.service.detail', $this->service->slug))
            ->assertOk()
            ->assertDontSee('Template Berkas');
    }

    public function test_admin_uploads_a_template_on_the_service_form(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(TemplatesRelationManager::class, ['ownerRecord' => $this->service, 'pageClass' => EditService::class])
            ->callTableAction('create', data: [
                'nama' => 'Surat pernyataan orang tua',
                'file_path' => UploadedFile::fake()->create('pernyataan.pdf', 20, 'application/pdf'),
                'is_required' => false,
                'sort' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $template = $this->service->templates()->firstOrFail();
        $this->assertSame('Surat pernyataan orang tua', $template->nama);
        $this->assertSame('pernyataan.pdf', $template->file_name);
        Storage::disk('local')->assertExists($template->file_path);
    }

    public function test_applicants_download_templates_while_applying(): void
    {
        $template = $this->template();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        $this->actingAs($applicant)
            ->get('/portal/ajukan?layanan=' . $this->service->slug)
            ->assertOk()
            ->assertSee('Template berkas')
            ->assertSee($template->downloadUrl(), false);
    }
}
