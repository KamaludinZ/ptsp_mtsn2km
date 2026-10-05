<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** The back-office unit roles (disposition recipients) in user management. */
class UserUnitRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_admin_assigns_a_unit_role_shown_by_its_label(): void
    {
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $staff = User::where('email', 'staff2@mtsn2malang.sch.id')->firstOrFail();
        $roleId = \Spatie\Permission\Models\Role::findByName('penjamin_mutu', 'web')->id;

        Livewire::test(EditUser::class, ['record' => $staff->getRouteKey()])
            ->assertSee('Penjamin Mutu')
            ->fillForm(['roles' => [$roleId]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($staff->fresh()->hasRole('penjamin_mutu'));
        $this->get(UserResource::getUrl('index'))->assertOk()->assertSee('Penjamin Mutu');
    }
}
