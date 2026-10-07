<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Tests\TestCase;

/** Buku tamu publik: nama tamu yang memilih disamarkan tidak pernah tampil utuh. */
class VisitorPublicNameTest extends TestCase
{
    public function test_obscured_names_are_masked_on_the_server(): void
    {
        $masked = (new Visitor(['name' => 'Budi Santoso', 'is_obscured' => true]))->publicName();

        $this->assertNotSame('Budi Santoso', $masked);
        $this->assertStringNotContainsString('Budi', $masked);
        $this->assertStringNotContainsString('Santoso', $masked);
        $this->assertSame(mb_strlen('Budi Santoso'), mb_strlen($masked));
        $this->assertStringStartsWith('B', $masked);
    }

    public function test_names_shown_as_is_when_the_guest_allows_it(): void
    {
        $this->assertSame('Budi Santoso', (new Visitor(['name' => 'Budi Santoso', 'is_obscured' => false]))->publicName());
    }
}
