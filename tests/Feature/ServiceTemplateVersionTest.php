<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceTemplateResource\Pages\ManageServiceTemplates;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Template berkas versions and status: replaced files raise the version, inactive templates leave the public pages. */
class ServiceTemplateVersionTest extends TestCase
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
        $this->service->templates()->get()->each->delete();
    }

    private function template(string $name, array $attributes = []): ServiceTemplate
    {
        Storage::disk('local')->put("service-templates/{$name}.docx", "isi {$name}");

        return $this->service->templates()->create([
            'nama' => ucfirst($name),
            'file_path' => "service-templates/{$name}.docx",
            'file_name' => "{$name}.docx",
            ...$attributes,
        ]);
    }

    public function test_replacing_the_file_raises_the_version(): void
    {
        $template = $this->template('formulir');
        $this->assertSame(1, $template->versi);
        $this->assertTrue($template->is_active);

        $template->update(['nama' => 'Formulir baru']);
        $this->assertSame(1, $template->fresh()->versi);

        Storage::disk('local')->put('service-templates/formulir-2026.docx', 'isi baru');
        $template->update(['file_path' => 'service-templates/formulir-2026.docx', 'file_name' => 'formulir-2026.docx']);
        $this->assertSame(2, $template->fresh()->versi);
    }

    public function test_inactive_templates_are_hidden_from_applicants_but_kept_for_staff(): void
    {
        $current = $this->template('baru', ['is_required' => true]);
        $old = $this->template('lama', ['is_active' => false, 'is_required' => true]);

        $this->get(route('onlineportal.service.detail', $this->service->slug))
            ->assertOk()->assertSee('Baru')->assertDontSee($old->downloadUrl(), false);
        $this->get($old->downloadUrl())->assertNotFound();
        $this->get($current->downloadUrl())->assertDownload('baru.docx');
        $this->assertSame([$current->id], $this->service->requiredTemplates()->pluck('id')->all());

        $this->getJson('/api/publik/layanan/' . $this->service->slug)
            ->assertOk()
            ->assertJsonCount(1, 'template_berkas')
            ->assertJsonPath('template_berkas.0.nama', 'Baru')
            ->assertJsonPath('template_berkas.0.versi', 1);

        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail())
            ->get($old->downloadUrl())->assertDownload('lama.docx');
    }

    public function test_admin_switches_a_template_off_from_the_list(): void
    {
        $template = $this->template('formulir');
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(ManageServiceTemplates::class)
            ->assertCanSeeTableRecords([$template])
            ->call('updateTableColumnState', 'is_active', (string) $template->getKey(), false)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$template]);

        $this->assertFalse($template->fresh()->is_active);
    }
}
