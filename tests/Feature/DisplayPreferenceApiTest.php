<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\DisplayPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** /api/preferensi-tampilan: theme mode and text size of the signed-in user. */
class DisplayPreferenceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_sign_in(): void
    {
        $this->getJson('/api/preferensi-tampilan')->assertUnauthorized();
        $this->patchJson('/api/preferensi-tampilan', ['mode' => 'dark'])->assertUnauthorized();
    }

    public function test_defaults_then_partial_updates(): void
    {
        $user = User::factory()->create(['preferences' => ['catatan' => 'tetap']]);
        Sanctum::actingAs($user);

        $this->getJson('/api/preferensi-tampilan')->assertOk()
            ->assertJsonPath('mode', 'system')->assertJsonPath('ukuran', 'normal')->assertJsonPath('diperbarui', null)
            ->assertJsonPath('pilihan.mode.2', ['nilai' => 'dark', 'label' => 'Gelap'])
            ->assertJsonPath('pilihan.ukuran.1.persen', 112.5);

        $this->patchJson('/api/preferensi-tampilan', ['mode' => 'dark'])->assertOk()
            ->assertJsonPath('message', 'Preferensi tampilan disimpan.')
            ->assertJsonPath('mode', 'dark')->assertJsonPath('mode_label', 'Gelap')->assertJsonPath('ukuran', 'normal');

        $this->patchJson('/api/preferensi-tampilan', ['ukuran' => 'xlarge'])->assertOk()
            ->assertJsonPath('mode', 'dark')->assertJsonPath('ukuran_persen', 125);

        $saved = DisplayPreferences::for($user->fresh());
        $this->assertSame(['dark', 'xlarge'], [$saved['mode'], $saved['size']]);
        $this->assertSame('tetap', $user->fresh()->preferences['catatan']); // other keys kept

        // The panels pick the size up on the next page
        $this->assertStringContainsString('font-size:125%', DisplayPreferences::headHtml($user->fresh()));
    }

    public function test_validation(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/preferensi-tampilan', ['mode' => 'biru', 'ukuran' => 'raksasa'])
            ->assertJsonValidationErrors(['mode' => 'system, light, dark', 'ukuran' => 'normal, large, xlarge']);
        $this->patchJson('/api/preferensi-tampilan', [])->assertUnprocessable()->assertJsonPath('message', 'Kirim mode dan/atau ukuran.');
    }
}
