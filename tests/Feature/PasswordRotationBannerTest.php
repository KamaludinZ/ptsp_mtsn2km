<?php

namespace Tests\Feature;

use App\Livewire\PasswordRotationBanner;
use App\Models\User;
use App\Support\PasswordRotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Banner pengingat ganti kata sandi di dalam panel. */
class PasswordRotationBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_shows_reminder_and_can_be_dismissed_for_the_session(): void
    {
        $this->app['env'] = 'local';
        $this->actingAs(User::factory()->create());
        filament()->setCurrentPanel(filament()->getPanel('portal'));

        Livewire::test(PasswordRotationBanner::class)
            ->assertSee('lebih dari 6 bulan')
            ->assertSee('Ganti kata sandi')
            ->call('dismiss')
            ->assertDontSee('Ganti kata sandi');

        $this->assertNull(PasswordRotation::reason(auth()->user()));
    }

    public function test_banner_stays_hidden_without_a_reason(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PasswordRotationBanner::class)->assertDontSee('Ganti kata sandi');
        $this->assertStringContainsString('kata sandi sementara', PasswordRotation::message(PasswordRotation::REASON_IMPORTED));
    }
}
