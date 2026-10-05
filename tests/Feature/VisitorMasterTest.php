<?php

namespace Tests\Feature;

use App\Models\VisitorMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Master buku tamu: the guest book offers and accepts the active choices. */
class VisitorMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_book_offers_active_destinations_with_other_last(): void
    {
        VisitorMaster::create(['type' => 'tujuan', 'nama' => 'Perpustakaan', 'sort' => 1]);
        VisitorMaster::where('type', 'tujuan')->where('nama', 'Komite')->update(['is_active' => false]);

        $options = VisitorMaster::options('tujuan');
        $this->assertContains('Perpustakaan', $options);
        $this->assertNotContains('Komite', $options);
        $this->assertSame('Lainnya', end($options));

        $this->get(route('public.visitor.book'))->assertOk()->assertSee('Perpustakaan')->assertDontSee('>Komite<', false);
    }

    public function test_guest_book_accepts_only_listed_destinations(): void
    {
        VisitorMaster::where('type', 'tujuan')->where('nama', 'Komite')->update(['is_active' => false]);
        $guest = ['name' => 'Tamu', 'phone' => '0812', 'purpose' => 'Komite'];

        $this->post(route('public.visitor.submit'), $guest)->assertSessionHasErrors('purpose');
        $this->post(route('public.visitor.submit'), ['purpose' => 'Waka Humas'] + $guest)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('visitors', ['name' => 'Tamu', 'purpose' => 'Waka Humas']);
    }

    public function test_both_lists_start_filled(): void
    {
        $this->assertContains('Kepala Madrasah', VisitorMaster::options('tujuan'));
        $this->assertContains('Konsultasi', VisitorMaster::options('keperluan'));
    }

    public function test_kiosk_signs_the_guest_book_through_the_api(): void
    {
        $this->getJson('/api/publik/buku-tamu/pilihan')->assertOk()->assertJsonPath('tujuan.0', 'Kepala Madrasah');

        $this->postJson('/api/publik/buku-tamu', ['name' => 'Pak Budi', 'phone' => '0813', 'purpose' => 'Lainnya', 'purpose_other' => 'Antar undangan', 'obscure_name' => true])
            ->assertCreated();
        $this->assertDatabaseHas('visitors', ['name' => 'Pak Budi', 'purpose' => 'Antar undangan', 'is_obscured' => true]);

        $this->postJson('/api/publik/buku-tamu', ['name' => 'X', 'phone' => '1', 'purpose' => 'Lainnya'])->assertJsonValidationErrors('purpose_other');
    }

    public function test_web_checkbox_hides_the_name(): void
    {
        $this->post(route('public.visitor.submit'), ['name' => 'Bu Ani', 'phone' => '0814', 'purpose' => 'Waka Humas', 'obscure_name' => 'on'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('visitors', ['name' => 'Bu Ani', 'is_obscured' => true]);
    }
}
