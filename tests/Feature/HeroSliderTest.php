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
            ->assertSeeInOrder(['Pertama', 'Daftar', 'Kedua']);
    }

    public function test_admin_adds_a_slide_with_an_image(): void
    {
        $this->actingAs($this->admin())->get(HeroSliderResource::getUrl('index'))->assertOk();

        Livewire::test(CreateHeroSlider::class)
            ->fillForm([
                'image' => UploadedFile::fake()->image('banner.jpg', 1920, 800),
                'title' => 'Selamat datang',
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
}
