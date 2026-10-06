<?php

namespace Tests\Feature;

use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\Service;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** TinyMCE rich text: one editor for every content field, safe HTML in and out. */
class TinyEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        config(['tinymce.api_key' => 'test-key']);
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_rich_text_is_sanitised_and_old_plain_text_reads_as_lists(): void
    {
        $this->assertSame('<ol><li>KTP</li><li>KK</li></ol>', RichText::toHtml('1. KTP 2. KK'));
        $this->assertSame('<ul><li>Akta</li><li>Rapor</li></ul>', RichText::toHtml('["Akta","Rapor"]'));
        $this->assertSame('<p>Baris satu<br>baris dua</p>', RichText::toHtml("Baris satu\nbaris dua"));

        $clean = RichText::sanitize('<p onclick="x()">Hai<script>alert(1)</script> <a href="javascript:alert(1)">tautan</a> <img src="https://ex.test/a.png" onerror="x()"></p>');
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertNull(RichText::sanitize('<p>&nbsp;</p>'));
        $this->assertSame('KTP KK', RichText::plain('<ol><li>KTP</li><li>KK</li></ol>'));
    }

    public function test_service_form_uses_tinymce_opens_old_text_as_html_and_saves_it_clean(): void
    {
        $this->actingAs($this->admin());
        $service = Service::where('is_active', true)->availableFor('umum')->firstOrFail();
        $service->update(['requirements' => '1. Formulir 2. Fotokopi KK']);

        $this->get(\App\Filament\Resources\ServiceResource::getUrl('edit', ['record' => $service]))
            ->assertOk()
            ->assertSee('ptspTinyEditor', false)
            ->assertSee('cdn.tiny.cloud', false)
            ->assertSee('test-key', false);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->assertFormSet(['requirements' => '<ol><li>Formulir</li><li>Fotokopi KK</li></ol>'])
            ->fillForm(['disposition_mode' => 'none', 'mechanism' => '<p>Daftar <strong>online</strong></p><script>alert(1)</script>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();
        $this->assertSame('<p>Daftar <strong>online</strong></p>', $service->mechanism);
        $this->assertSame('<ol><li>Formulir</li><li>Fotokopi KK</li></ol>', $service->requirements);

        $this->get(route('onlineportal.service.detail', $service->slug))
            ->assertOk()
            ->assertSee('<strong>online</strong>', false)
            ->assertSee('<li>Fotokopi KK</li>', false);
    }

    public function test_faq_and_announcements_render_rich_text_safely(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateFaq::class)
            ->fillForm(['question' => 'Bagaimana cara mengajukan?', 'answer' => '<ul><li>Masuk portal</li></ul><img src=x onerror=alert(1)>', 'category' => 'layanan', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();
        $faq = Faq::where('question', 'Bagaimana cara mengajukan?')->firstOrFail();
        $this->assertStringNotContainsString('onerror', $faq->answer);
        $this->assertStringContainsString('<li>Masuk portal</li>', (string) $faq->safeAnswer());

        $item = Pengumuman::create(['title' => 'Libur', 'content' => '<p>Kantor <em>tutup</em></p>', 'is_active' => true, 'publish_date' => today(), 'user_id' => $this->admin()->id]);
        $this->get(route('pengumuman.show', $item))->assertOk()->assertSee('<em>tutup</em>', false);
    }

    public function test_staff_upload_editor_images_and_others_cannot(): void
    {
        Storage::fake('public');

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail())
            ->post(route('editor.upload'), ['file' => UploadedFile::fake()->image('foto.png')], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonStructure(['location'])
            ->assertJsonPath('location', fn (string $url) => str_starts_with($url, '/storage/editor-uploads/'));
        $this->post(route('editor.upload'), ['file' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $applicant = tap(User::factory()->create())->assignRole('umum');
        $this->actingAs($applicant)
            ->post(route('editor.upload'), ['file' => UploadedFile::fake()->image('foto.png')], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    public function test_content_security_policy_allows_tinymce_cloud(): void
    {
        $policy = $this->actingAs($this->admin())->get(\App\Filament\Resources\FaqResource::getUrl('create'))->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('https://cdn.tiny.cloud', $policy);
    }

    public function test_alignment_lists_and_formatting_reach_the_public_service_pages(): void
    {
        $service = Service::where('is_active', true)->availableFor('umum')->firstOrFail();
        $service->update([
            'description' => '<p style="text-align: center;">Layanan <strong>cepat</strong></p>',
            'requirements' => '<ul><li>KTP</li><li>KK</li></ul><ol><li>Isi formulir</li></ol>',
            'mechanism' => '<p style="text-align: justify; background: url(javascript:alert(1)); position: fixed;">Rata kanan-kiri</p><p style="text-align: right;">Kanan</p><p style="text-align: left;">Kiri</p>',
        ]);

        $this->get(route('onlineportal.service.detail', $service->slug))
            ->assertOk()
            ->assertSee('<p style="text-align: center;">Layanan <strong>cepat</strong></p>', false)
            ->assertSee('<ul><li>KTP</li><li>KK</li></ul>', false)
            ->assertSee('<ol><li>Isi formulir</li></ol>', false)
            ->assertSee('<p style="text-align: justify;">Rata kanan-kiri</p>', false)
            ->assertSee('<p style="text-align: right;">Kanan</p>', false)
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('position: fixed', false);

        $this->get(route('onlineportal.service.catalog'))->assertOk()->assertDontSee('&lt;p', false);
    }
}
