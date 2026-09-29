<?php

namespace Tests\Feature;

use App\Filament\Shared\Pages\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Account settings live in each panel (/cp/profile for staff, /portal/profile for applicants). */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Filament::setCurrentPanel(Filament::getPanel('portal'));
    }

    public function test_profile_page_is_displayed_in_the_users_panel(): void
    {
        $applicant = User::factory()->create();
        $this->actingAs($applicant)->get('/profile')->assertRedirect('/portal/profile');
        $this->actingAs($applicant)->get('/portal/profile')->assertOk();

        $staff = User::factory()->create(['user_type' => 'pegawai']);
        $staff->assignRole(Role::findOrCreate('back_office'));
        $this->actingAs($staff)->get('/profile')->assertRedirect('/cp/profile');
        $this->actingAs($staff)->get('/cp/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Test User', 'email' => 'test@example.com', 'whatsapp_number' => '081200000000'])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('081200000000', $user->whatsapp_number);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Test User', 'email' => $user->email])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->callAction('deleteAccount', ['password' => 'password'])
            ->assertHasNoActionErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertTrue($user->fresh() === null || $user->fresh()->trashed());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->callAction('deleteAccount', ['password' => 'wrong-password'])
            ->assertHasActionErrors(['password']);

        $this->assertNotNull($user->fresh());
        $this->assertFalse($user->fresh()->trashed());
    }

    public function test_staff_cannot_delete_their_own_account(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $staff = User::factory()->create(['user_type' => 'pegawai']);
        $staff->assignRole(Role::findOrCreate('back_office'));
        $this->actingAs($staff);

        Livewire::test(EditProfile::class)->assertActionHidden('deleteAccount');
    }
}
