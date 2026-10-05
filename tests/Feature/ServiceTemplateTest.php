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

    public function test_service_page_without_templates_says_none_is_needed(): void
    {
        $this->get(route('onlineportal.service.detail', $this->service->slug))
            ->assertOk()
            ->assertDontSee('Unduh semua (.zip)')
            ->assertDontSee('templates-heading', false)
            ->assertSee('Layanan ini tidak memakai template berkas');

        $this->getJson('/api/publik/layanan/' . $this->service->slug)->assertOk()
            ->assertJsonPath('perlu_template', false)
            ->assertJsonCount(0, 'template_berkas');
    }

    public function test_missing_template_files_are_not_offered_for_download(): void
    {
        $template = $this->template();
        Storage::disk('local')->delete($template->file_path);

        $this->getJson('/api/publik/layanan/' . $this->service->slug)->assertOk()
            ->assertJsonPath('perlu_template', true)
            ->assertJsonPath('template_berkas.0.tersedia', false)
            ->assertJsonPath('template_berkas.0.unduh', null);

        $this->actingAs(User::where('email', 'budi.santoso@email.com')->firstOrFail())
            ->get('/portal/ajukan?layanan=' . $this->service->slug)
            ->assertOk()
            ->assertSee('sedang tidak tersedia, hubungi petugas')
            ->assertDontSee($template->downloadUrl(), false);
    }

    public function test_catalog_filter_finds_services_without_templates(): void
    {
        $this->template();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(\App\Filament\Resources\ServiceResource\Pages\ListServices::class)
            ->filterTable('has_templates', true)
            ->assertCountTableRecords(1)
            ->assertCanSeeTableRecords([$this->service])
            ->filterTable('has_templates', false)
            ->assertCanNotSeeTableRecords([$this->service]);
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

    public function test_admin_manages_every_template_on_one_page(): void
    {
        $template = $this->template();
        $other = Service::where('id', '!=', $this->service->id)->firstOrFail();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $page = \App\Filament\Resources\ServiceTemplateResource\Pages\ManageServiceTemplates::class;

        $this->get(\App\Filament\Resources\ServiceTemplateResource::getUrl())->assertOk()
            ->assertSee('Template Berkas Layanan')->assertSee('1 layanan memiliki template');

        Livewire::test($page)
            ->assertCanSeeTableRecords([$template])
            ->assertSee('docx · 1 KB')
            ->callAction('create', [
                'service_id' => $other->id,
                'nama' => 'Surat pernyataan',
                'file_path' => [UploadedFile::fake()->create('pernyataan.pdf', 20, 'application/pdf')],
                'is_required' => false,
            ])
            ->assertHasNoActionErrors();

        $created = ServiceTemplate::where('nama', 'Surat pernyataan')->firstOrFail();
        $this->assertSame($other->id, $created->service_id);
        $this->assertSame('pernyataan.pdf', $created->file_name);

        Livewire::test($page)
            ->filterTable('service_id', $other->id)
            ->assertCanSeeTableRecords([$created])
            ->assertCanNotSeeTableRecords([$template])
            ->callTableAction('delete', $created);
        $this->assertModelMissing($created);
        Storage::disk('local')->assertMissing($created->file_path);
    }

    public function test_replacing_a_template_file_removes_the_old_one(): void
    {
        $template = $this->template();
        Storage::disk('local')->put('service-templates/baru.pdf', 'baru');

        $template->update(['file_path' => 'service-templates/baru.pdf', 'file_name' => 'baru.pdf']);

        Storage::disk('local')->assertMissing('service-templates/formulir.docx');
        Storage::disk('local')->assertExists('service-templates/baru.pdf');
    }

    public function test_only_admins_open_the_template_page(): void
    {
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->get(\App\Filament\Resources\ServiceTemplateResource::getUrl())->assertForbidden();
    }

    public function test_template_names_are_unique_per_service_in_both_forms(): void
    {
        $template = $this->template();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $upload = fn () => [UploadedFile::fake()->create('lain.pdf', 10, 'application/pdf')];

        Livewire::test(\App\Filament\Resources\ServiceTemplateResource\Pages\ManageServiceTemplates::class)
            ->callAction('create', ['service_id' => $this->service->id, 'nama' => 'Formulir permohonan', 'file_path' => $upload()])
            ->assertHasActionErrors(['nama' => 'unique']);

        Livewire::test(TemplatesRelationManager::class, ['ownerRecord' => $this->service, 'pageClass' => EditService::class])
            ->callTableAction('create', data: ['nama' => 'Formulir permohonan', 'file_path' => $upload()])
            ->assertHasTableActionErrors(['nama' => 'unique']);

        // Same name on another service is fine; editing a template keeps its own name.
        $other = Service::where('id', '!=', $this->service->id)->firstOrFail();
        Livewire::test(\App\Filament\Resources\ServiceTemplateResource\Pages\ManageServiceTemplates::class)
            ->callAction('create', ['service_id' => $other->id, 'nama' => 'Formulir permohonan', 'file_path' => $upload()])
            ->assertHasNoActionErrors()
            ->mountTableAction('edit', $template)
            ->assertSee('formulir-permohonan.docx')
            ->setTableActionData(['is_required' => false])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertFalse($template->fresh()->is_required);
        $this->assertSame(2, ServiceTemplate::where('nama', 'Formulir permohonan')->count());
    }

    public function test_all_templates_download_as_one_zip(): void
    {
        $this->template();
        Storage::disk('local')->put('service-templates/kedua.pdf', 'pdf');
        $this->service->templates()->create(['nama' => 'Surat pernyataan', 'file_path' => 'service-templates/kedua.pdf', 'file_name' => 'formulir-permohonan.docx', 'sort' => 2]);
        $this->service->templates()->create(['nama' => 'Hilang', 'file_path' => 'service-templates/hilang.pdf', 'file_name' => 'hilang.pdf', 'sort' => 3]);

        $this->get(route('onlineportal.service.detail', $this->service->slug))->assertOk()
            ->assertSee('Unduh semua (.zip)')
            ->assertSee('3 template untuk layanan ini, 1 wajib dilengkapi')
            ->assertSee('Berkas sedang tidak tersedia')
            ->assertSee('fa-file-word', false);

        $response = $this->get(route('onlineportal.service.templates.zip', $this->service->slug))->assertOk();
        $path = $response->getFile()->getPathname();
        $zip = new \ZipArchive();
        $zip->open($path);
        $names = collect(range(0, $zip->numFiles - 1))->map(fn ($i) => $zip->getNameIndex($i))->all();
        $zip->close();

        // Same file name twice is numbered; the missing file is left out.
        $this->assertEqualsCanonicalizing(['formulir-permohonan.docx', 'formulir-permohonan (2).docx'], $names);
    }

    public function test_zip_is_not_offered_for_inactive_services_or_services_without_files(): void
    {
        $this->service->update(['is_active' => false]);
        $this->get(route('onlineportal.service.templates.zip', $this->service->slug))->assertNotFound();

        $other = Service::where('is_active', true)->whereDoesntHave('templates')->firstOrFail();
        $this->get(route('onlineportal.service.templates.zip', $other->slug))->assertNotFound();
    }

    public function test_type_and_size_are_stored_and_names_are_unique_in_the_database(): void
    {
        $template = $this->template();
        $this->assertSame(strlen('isi template'), $template->file_size);
        $this->assertNotNull($template->mime_type);

        Storage::disk('local')->put('service-templates/besar.pdf', str_repeat('x', 4096));
        $template->update(['file_path' => 'service-templates/besar.pdf', 'file_name' => 'besar.pdf']);
        $this->assertSame(4096, $template->fresh()->file_size);
        $this->assertSame('pdf · 4 KB', $template->fresh()->fileInfo());

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        \Illuminate\Support\Facades\DB::table('service_templates')->insert([
            'service_id' => $this->service->id, 'nama' => $template->nama, 'file_path' => 'x', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_existing_templates_are_backfilled_and_duplicates_renamed(): void
    {
        $migration = require database_path('migrations/2026_10_06_170000_harden_service_templates_table.php');
        $migration->down();
        Storage::disk('local')->put('service-templates/a.docx', 'abc');
        foreach ([1, 2] as $i) {
            \Illuminate\Support\Facades\DB::table('service_templates')->insert([
                'service_id' => $this->service->id, 'nama' => 'Formulir', 'file_path' => 'service-templates/a.docx', 'file_name' => 'a.docx',
                'is_required' => false, 'sort' => $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $migration->up();

        $this->assertSame(['Formulir', 'Formulir (2)'], ServiceTemplate::orderBy('id')->pluck('nama')->all());
        $this->assertSame([3, 3], ServiceTemplate::orderBy('id')->pluck('file_size')->all());
    }

    public function test_relations_and_scopes(): void
    {
        $required = $this->template(['sort' => 5]);
        Storage::disk('local')->put('service-templates/b.pdf', 'b');
        $optional = $this->service->templates()->create(['nama' => 'Opsional', 'file_path' => 'service-templates/b.pdf', 'is_required' => false, 'sort' => 1]);

        $this->assertSame([$optional->id, $required->id], $this->service->templates()->pluck('id')->all());
        $this->assertSame([$required->id], $this->service->requiredTemplates()->pluck('id')->all());
        $this->assertSame([$required->id], ServiceTemplate::required()->pluck('id')->all());
        $this->assertSame($this->service->id, $optional->service->id);
    }

    public function test_permanently_deleting_a_service_removes_its_template_files(): void
    {
        $service = Service::factory()->create();
        Storage::disk('local')->put('service-templates/hapus.docx', 'x');
        $template = $service->templates()->create(['nama' => 'Hapus', 'file_path' => 'service-templates/hapus.docx']);

        $service->delete(); // soft delete keeps everything
        Storage::disk('local')->assertExists('service-templates/hapus.docx');

        $service->forceDelete();
        $this->assertModelMissing($template);
        Storage::disk('local')->assertMissing('service-templates/hapus.docx');
        $this->assertNull((new ServiceTemplate(['service_id' => 999999]))->downloadUrl());
    }
}
