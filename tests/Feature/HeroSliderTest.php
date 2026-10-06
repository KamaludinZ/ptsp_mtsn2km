<?php

namespace Tests\Feature;

use App\Filament\Resources\HeroSliderResource;
use App\Filament\Resources\HeroSliderResource\Pages\CreateHeroSlider;
use App\Filament\Resources\HeroSliderResource\Pages\ListHeroSliders;
use App\Models\HeroSlider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Hero slider beranda: managed by the admin, shown on the homepage only when a slide is active. */
class HeroSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('public');
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    private function slide(array $attributes = []): HeroSlider
    {
        Storage::disk('public')->put('hero-slides/a.jpg', 'gambar');

        return HeroSlider::create(['image' => 'hero-slides/a.jpg', 'title' => 'Penerimaan Siswa Baru', ...$attributes]);
    }

    public function test_homepage_keeps_the_static_hero_without_active_slides(): void
    {
        $this->slide(['is_active' => false]);

        $this->get('/')->assertOk()->assertSee('id="hero-section"', false)->assertDontSee('id="hero-slider"', false);
    }

    public function test_homepage_shows_active_slides_in_order(): void
    {
        $this->slide(['title' => 'Kedua', 'sort_order' => 2]);
        $this->slide(['title' => 'Pertama', 'sort_order' => 1, 'button1_text' => 'Daftar', 'button1_url' => '/layanan']);

        $this->get('/')->assertOk()
            ->assertSee('id="hero-slider"', false)
            ->assertDontSee('id="hero-section"', false)
            ->assertSeeInOrder(['Pertama', 'Daftar', 'Kedua'])
            // Same-origin path whatever APP_URL is (img-src 'self' in the CSP)
            ->assertSee('src="/storage/hero-slides/a.jpg"', false)
            ->assertSee('alt="Pertama"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('loading="lazy"', false);
    }

    public function test_admin_adds_a_slide_with_an_image(): void
    {
        $this->actingAs($this->admin())->get(HeroSliderResource::getUrl('index'))->assertOk();

        Livewire::test(CreateHeroSlider::class)
            ->fillForm([
                'image' => UploadedFile::fake()->image('banner.jpg', 1920, 800),
                'title' => 'Selamat datang',
                'text_color' => '#fefefe',
                'overlay_color' => 'rgba(20,83,45,0.55)',
                'image_fit' => 'cover',
                'zoom_effect' => 'out',
                'text_backdrop' => 'dark',
                'button1_text' => 'Ajukan',
                'button1_url' => 'javascript:alert(1)',
            ])
            ->call('create')
            ->assertHasFormErrors(['button1_url'])
            ->fillForm(['button1_url' => '/layanan'])
            ->call('create')
            ->assertHasNoFormErrors();

        $slide = HeroSlider::where('title', 'Selamat datang')->firstOrFail();
        $this->assertTrue($slide->is_active);
        $this->assertSame(['#fefefe', 'rgba(20,83,45,0.55)'], [$slide->text_color, $slide->overlay_color]);
        $this->assertSame(['cover', 'out', 'dark'], [$slide->image_fit, $slide->zoom_effect, $slide->text_backdrop]);
        $this->assertStringStartsWith('/storage/hero-slides/', \Illuminate\Support\Facades\Storage::disk('public')->url($slide->image));
        Storage::disk('public')->assertExists($slide->image);
    }

    public function test_admin_hides_a_slide_and_other_staff_cannot_manage_slides(): void
    {
        $slide = $this->slide();
        $this->actingAs($this->admin());
        Livewire::test(ListHeroSliders::class)->call('updateTableColumnState', 'is_active', (string) $slide->getKey(), false);
        $this->assertFalse($slide->fresh()->is_active);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail())
            ->get(HeroSliderResource::getUrl('index'))->assertForbidden();
    }

    public function test_public_api_lists_active_slides(): void
    {
        $this->slide(['title' => 'Tersembunyi', 'is_active' => false]);
        $this->slide(['title' => 'PPDB', 'button1_text' => 'Daftar', 'button1_url' => '/layanan', 'button2_text' => 'Tanpa tautan']);

        $this->getJson('/api/publik/slider')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'PPDB')
            ->assertJsonPath('data.0.tombol', [['teks' => 'Daftar', 'tautan' => url('/layanan')]]);
    }

    public function test_phones_get_the_portrait_image_and_replaced_images_are_removed(): void
    {
        Storage::disk('public')->put('hero-slides/hp.jpg', 'potret');
        $slide = $this->slide(['title' => 'Dengan gambar HP', 'image_mobile' => 'hero-slides/hp.jpg']);

        $this->get('/')->assertOk()
            ->assertSee('<source media="(max-width: 767.98px)" srcset="/storage/hero-slides/hp.jpg"', false)
            ->assertSee('src="/storage/hero-slides/a.jpg"', false);

        Storage::disk('public')->put('hero-slides/hp2.jpg', 'potret baru');
        $slide->update(['image_mobile' => 'hero-slides/hp2.jpg']);
        Storage::disk('public')->assertMissing('hero-slides/hp.jpg');

        $slide->delete();
        Storage::disk('public')->assertMissing('hero-slides/hp2.jpg');
        Storage::disk('public')->assertMissing('hero-slides/a.jpg');
    }

    public function test_slides_carry_their_fit_zoom_and_text_backdrop(): void
    {
        $whole = $this->slide(['title' => 'Utuh', 'sort_order' => 1]);
        $this->slide(['title' => 'Penuh', 'sort_order' => 2, 'image_fit' => 'cover', 'zoom_effect' => 'none', 'text_backdrop' => 'gradient']);

        $this->assertSame('hero-slide--fit-contain hero-slide--zoom-in hero-slide--text-glass', $whole->displayClasses());
        $this->assertSame('hero-slide--fit-contain hero-slide--zoom-in hero-slide--text-glass', (new \App\Models\HeroSlider(['image_fit' => 'x', 'zoom_effect' => 'y', 'text_backdrop' => 'z']))->displayClasses());

        $this->get('/')->assertOk()
            ->assertSee('hero-slide hero-slide--fit-contain hero-slide--zoom-in hero-slide--text-glass', false)
            ->assertSee('hero-slide hero-slide--fit-cover hero-slide--zoom-none hero-slide--text-gradient', false)
            ->assertSee('class="hero-slide__blur" aria-hidden="true"', false)
            ->assertSee('class="hero-slide__panel"', false);
    }
}
