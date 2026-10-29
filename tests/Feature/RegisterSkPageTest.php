<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Register Penomoran SK (/cp/penomoran-sk): the page and where it sits in the menu. */
class RegisterSkPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_back_office_opens_the_register_page(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))
            ->get(SuratKeputusanResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Register Penomoran SK')
            ->assertSee('{N} TAHUN {YYYY}')
            ->assertSee('terpisah dari nomor surat keluar');

        $this->assertStringEndsWith('/cp/penomoran-sk', SuratKeputusanResource::getUrl('index'));
    }

    public function test_empty_register_explains_what_will_appear(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        \App\Models\SuratKeputusan::withDeletesAllowed(fn () => \App\Models\SuratKeputusan::query()->delete());

        Livewire::test(ListSuratKeputusan::class)
            ->assertOk()
            ->assertSee('Belum ada nomor SK');
    }

    public function test_menu_sits_in_persuratan_right_below_surat_keluar(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))->get('/cp')->assertOk();

        $items = collect(Filament::getPanel('admin')->getNavigation())
            ->firstWhere(fn ($group) => $group->getLabel() === 'Persuratan')
            ->getItems()
            ->map(fn ($item) => $item->getLabel())
            ->values()
            ->all();

        $this->assertSame(['Surat Keluar', 'Penomoran SK', 'Master Persuratan'], array_slice($items, 0, 3));
    }

    public function test_front_desk_does_not_see_or_open_the_register(): void
    {
        $frontDesk = $this->user('loket1@mtsn2malang.sch.id');

        $this->actingAs($frontDesk)->get('/cp')->assertOk()->assertDontSee('Penomoran SK');
        $this->get(SuratKeputusanResource::getUrl('index'))->assertForbidden();
    }

    public function test_guide_describes_the_register(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'))
            ->get(\App\Filament\Pages\Guide::getUrl(panel: 'admin'))
            ->assertOk()
            ->assertSee('Buku register Surat Keputusan (SK)');
    }
}
