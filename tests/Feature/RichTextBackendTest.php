<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Pengumuman;
use App\Models\Service;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every backend path keeps rich text by the same rules (App\Support\RichText)
 * as the panel's editor and the public pages: no script, no class or id of
 * a pasted page, only the allowed styles.
 */
class RichTextBackendTest extends TestCase
{
    use RefreshDatabase;

    private const PASTED = '<div class="tab-pane fade" id="syarat" style="position: fixed; color: #e03e2d;"><p class="MsoNormal">Isi</p></div><script>alert(1)</script>';

    private const CLEAN = '<div style="color: #e03e2d;"><p>Isi</p></div>';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_the_service_api_saves_rich_text_as_the_editor_does(): void
    {
        Sanctum::actingAs($this->admin());
        $service = Service::where('code', 'SKSA-001')->firstOrFail();

        $this->patchJson("/api/layanan-ptsp/{$service->id}", ['deskripsi' => self::PASTED, 'mekanisme' => self::PASTED, 'persyaratan' => '<p>&nbsp;</p>'])->assertOk();

        $service->refresh();
        $this->assertSame(self::CLEAN, $service->description);
        $this->assertSame(self::CLEAN, $service->mechanism);
        $this->assertNull($service->getAttributes()['requirements']);
    }

    public function test_the_faq_and_announcement_apis_save_rich_text_as_the_editor_does(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/faq', ['pertanyaan' => 'Jam layanan?', 'jawaban' => self::PASTED])->assertCreated();
        $this->assertSame(self::CLEAN, Faq::where('question', 'Jam layanan?')->firstOrFail()->answer);

        $this->postJson('/api/pengumuman', ['judul' => 'Libur', 'isi' => self::PASTED, 'kategori' => 'umum', 'tanggal_tayang' => today()->toDateString()])->assertCreated();
        $this->assertSame(self::CLEAN, Pengumuman::where('title', 'Libur')->firstOrFail()->content);
        $this->postJson('/api/pengumuman', ['judul' => 'Kosong', 'isi' => '<script>x</script>', 'kategori' => 'umum', 'tanggal_tayang' => today()->toDateString()])
            ->assertJsonValidationErrors(['isi' => 'Isi pengumuman tidak boleh kosong.']);
    }

    public function test_the_standard_components_are_saved_clean(): void
    {
        $service = Service::where('code', 'SKSA-001')->firstOrFail();
        $type = array_key_first(\App\Models\ServiceComponent::EXTRA);

        $service->syncStandardComponents([$type => self::PASTED]);
        $this->assertSame(self::CLEAN, $service->components()->where('component_type', $type)->value('content'));

        $service->syncStandardComponents([$type => '<p>&nbsp;</p>']);
        $this->assertFalse($service->components()->where('component_type', $type)->exists());
    }

    public function test_the_public_api_gives_clean_rich_text_and_keeps_plain_text(): void
    {
        $service = Service::where('is_active', true)->availableFor('umum')->firstOrFail();
        $service->forceFill(['description' => self::PASTED, 'mechanism' => '1. Ajukan 2. Ambil'])->saveQuietly();
        $announcement = Pengumuman::create(['title' => 'Uji', 'content' => self::PASTED, 'category' => 'umum', 'publish_date' => today()->subDay(), 'is_active' => true]);

        $this->getJson('/api/publik/layanan/' . $service->slug)->assertOk()
            ->assertJsonPath('deskripsi', self::CLEAN)
            ->assertJsonPath('mekanisme', '1. Ajukan 2. Ambil');
        $this->getJson('/api/publik/pengumuman/' . $announcement->id)->assertOk()->assertJsonPath('isi', self::CLEAN);
        $this->getJson('/api/publik/pengumuman')->assertOk()->assertJsonPath('data.0.ringkasan', 'Isi');
    }

    public function test_plain_text_never_shows_the_text_of_a_script(): void
    {
        $this->assertSame('Isi', RichText::plain(self::PASTED));
        $this->assertSame('<p>Isi</p>', RichText::forApi('<p>Isi</p><style>p{display:none}</style>'));
        $this->assertSame('nilai minimal 70', RichText::forApi('nilai minimal 70'));
    }
}
