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

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $user = User::factory()->create(['user_type' => 'umum']);
        $this->actingAs($user);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('portal'));
        $page = \App\Filament\Shared\Pages\EditProfile::class;

        Livewire::test($page)
            ->fillForm(['password' => 'BaruSekali123', 'passwordConfirmation' => 'BaruSekali123'])
            ->call('save')
            ->assertHasFormErrors(['current_password' => 'required']);

        Livewire::test($page)
            ->fillForm(['current_password' => 'salah', 'password' => 'BaruSekali123', 'passwordConfirmation' => 'BaruSekali123'])
            ->call('save')
            ->assertHasFormErrors(['current_password']);

        Livewire::test($page)
            ->fillForm(['current_password' => 'password', 'password' => 'pendek', 'passwordConfirmation' => 'pendek'])
            ->call('save')
            ->assertHasFormErrors(['password']);

        Livewire::test($page)
            ->fillForm(['current_password' => 'password', 'password' => 'BaruSekali123', 'passwordConfirmation' => 'BaruSekali123'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('BaruSekali123', $user->fresh()->password));
        $this->assertDatabaseHas('activity_log', ['causer_id' => $user->id, 'description' => 'Mengganti kata sandi']);
    }

    public function test_reset_password_page_is_in_indonesian_with_the_same_rules(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk()
            ->assertSee('Atur Ulang Password')
            ->assertSee('Simpan Password Baru')
            ->assertSee('autocomplete="new-password"', false)
            ->assertSee('Minimal 8 karakter');

        $this->post(route('password.store'), ['token' => $token, 'email' => $user->email, 'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'])
            ->assertSessionHasErrors('password');
        $this->post(route('password.store'), ['token' => $token, 'email' => $user->email, 'password' => 'abcdefg1', 'password_confirmation' => 'abcdefg1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('abcdefg1', $user->fresh()->password));

        $this->get(route('password.request'))->assertOk()->assertSee('Lupa Password?');
    }

    public function test_display_preferences_are_saved_and_applied_in_the_panel(): void
    {
        \App\Support\RoleAccess::sync();
        $user = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditProfile::class)
            ->assertFormSet(['display_mode' => 'system', 'display_size' => 'normal'])
            ->fillForm(['display_mode' => 'dark', 'display_size' => 'large'])
            ->call('save')
            ->assertHasNoFormErrors();

        $prefs = \App\Support\DisplayPreferences::for($user->fresh());
        $this->assertSame(['dark', 'large'], [$prefs['mode'], $prefs['size']]);
        $this->assertNotNull($prefs['updated_at']);

        $this->get('/cp')->assertOk()
            ->assertSee('<style>html{font-size:112.5%}</style>', false)
            ->assertSee('localStorage.setItem("theme","dark")', false);

        // Saving again without changes keeps the same timestamp (the panel's own theme switcher is not overridden).
        Livewire::test(EditProfile::class)->fillForm(['name' => 'Nama Baru'])->call('save');
        $this->assertSame($prefs['updated_at'], \App\Support\DisplayPreferences::for($user->fresh())['updated_at']);
    }

    public function test_default_preferences_add_nothing_to_the_page(): void
    {
        \App\Support\RoleAccess::sync();
        $user = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');

        $this->actingAs($user)->get('/cp')->assertOk()->assertDontSee('display-pref-at', false)->assertDontSee('html{font-size', false);
    }
}
