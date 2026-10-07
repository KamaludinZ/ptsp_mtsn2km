<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Buku tamu publik: nama tamu yang memilih disamarkan tidak pernah tampil utuh. */
class VisitorPublicNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_obscured_names_are_masked_on_the_server(): void
    {
        $masked = (new Visitor(['name' => 'Budi Santoso', 'is_obscured' => true]))->publicName();

        $this->assertNotSame('Budi Santoso', $masked);
        $this->assertStringNotContainsString('Budi', $masked);
        $this->assertStringNotContainsString('Santoso', $masked);
        $this->assertSame(mb_strlen('Budi Santoso'), mb_strlen($masked));
        $this->assertStringStartsWith('B', $masked);
    }

    public function test_public_visitor_book_never_shows_a_masked_guests_name_but_staff_do(): void
    {
        Visitor::create(['name' => 'Rahasia Sekali', 'phone' => '0812', 'purpose' => 'Komite', 'check_in_time' => now(), 'is_obscured' => true]);
        Visitor::create(['name' => 'Tampil Biasa', 'phone' => '0813', 'purpose' => 'Komite', 'check_in_time' => now(), 'is_obscured' => false]);

        $this->get('/visitor-book')->assertOk()
            ->assertDontSee('Rahasia Sekali')
            ->assertSee('Tampil Biasa');

        $officer = \App\Models\User::factory()->create();
        $officer->assignRole(\Spatie\Permission\Models\Role::findOrCreate('front_desk', 'web'));
        \App\Support\RoleAccess::sync();
        \Laravel\Sanctum\Sanctum::actingAs($officer);
        // Staff working the guest book see the real name.
        $this->assertContains('Rahasia Sekali', collect($this->getJson('/api/buku-tamu')->assertOk()->json('data'))->pluck('nama')->all());
    }

    public function test_names_shown_as_is_when_the_guest_allows_it(): void
    {
        $this->assertSame('Budi Santoso', (new Visitor(['name' => 'Budi Santoso', 'is_obscured' => false]))->publicName());
    }
}
