<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Form tambah dan ubah pengguna. */
class UserFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_admin_creates_a_verified_officer_with_a_strong_password(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Rina Petugas', 'email' => 'rina@mtsn2malang.sch.id', 'whatsapp_number' => '0812-3456-7890', 'user_type' => 'pegawai',
                'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'roles' => [Role::findByName('back_office')->id], 'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'rina@mtsn2malang.sch.id')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertSame('081234567890', $user->whatsapp_number);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('back_office'));
    }

    public function test_weak_or_mismatched_passwords_and_bad_numbers_are_refused(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'X', 'email' => 'x@contoh.id', 'user_type' => 'umum', 'whatsapp_number' => '12345',
                'password' => 'pendek', 'password_confirmation' => 'lain'])
            ->call('create')
            ->assertHasFormErrors(['password', 'whatsapp_number' => 'regex']);
    }

    public function test_password_is_kept_when_left_empty_on_edit(): void
    {
        $user = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $hash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Staf TU Satu'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$hash, 'Staf TU Satu'], [$user->fresh()->password, $user->fresh()->name]);
    }

    public function test_admin_cannot_lock_themselves_out(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertFormFieldIsDisabled('is_active')
            ->assertActionHidden('delete')
            ->fillForm(['roles' => [Role::findByName('back_office')->id]])
            ->call('save')
            ->assertHasFormErrors(['roles']);

        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
        $this->assertTrue($this->admin->fresh()->is_active);

        // Other accounts can still be deactivated and deleted.
        $other = User::where('email', 'staff2@mtsn2malang.sch.id')->firstOrFail();
        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->assertFormFieldIsEnabled('is_active')
            ->assertActionVisible('delete');
    }
}
