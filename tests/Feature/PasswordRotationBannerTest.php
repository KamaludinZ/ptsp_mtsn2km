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

    protected function setUp(): void
    {
        parent::setUp();

        filament()->setCurrentPanel(filament()->getPanel('portal'));
    }

    public function test_new_accounts_record_when_their_password_was_set_and_see_no_banner(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->password_changed_at);
        $this->assertNull(PasswordRotation::reason($user));

        $this->actingAs($user);
        Livewire::test(PasswordRotationBanner::class)->assertDontSee('Ganti kata sandi');
    }

    public function test_password_older_than_six_months_shows_the_periodic_reminder_until_dismissed(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['password_changed_at' => now()->subMonths(6)->subDay()])->save();
        $this->actingAs($user);

        Livewire::test(PasswordRotationBanner::class)
            ->assertSee('lebih dari 6 bulan')
            ->assertSee('Ganti kata sandi')
            ->call('dismiss')
            ->assertDontSee('Ganti kata sandi');

        // Hanya untuk sesi ini: sesi berikutnya diingatkan lagi.
        session()->forget(PasswordRotation::DISMISS_KEY);
        $this->assertSame(PasswordRotation::REASON_PERIODIC, PasswordRotation::reason($user->fresh()));
    }

    public function test_imported_account_is_reminded_once(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['must_change_password' => true])->save();
        $this->actingAs($user);

        Livewire::test(PasswordRotationBanner::class)
            ->assertSee('kata sandi sementara')
            ->call('dismiss');

        session()->forget(PasswordRotation::DISMISS_KEY);
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertNull(PasswordRotation::reason($user->fresh()));
    }

    public function test_changing_the_password_restarts_the_cycle(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['password_changed_at' => now()->subYear(), 'must_change_password' => true])->save();

        $user->update(['password' => 'KataSandiBaru123']);

        $user = $user->fresh();
        $this->assertTrue($user->password_changed_at->isToday());
        $this->assertFalse($user->must_change_password);
        $this->assertNull(PasswordRotation::reason($user));
    }
}
