<?php

namespace Tests\Feature;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Daftar peran dan izin. */
class RoleListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_roles_show_their_work_areas_and_link_to_their_users(): void
    {
        $backOffice = Role::findByName('back_office');

        $this->get(RoleResource::getUrl())->assertOk()->assertSee('Peta izin');

        Livewire::test(ListRoles::class)
            ->assertTableColumnStateSet('areas', ['Back Office'], (string) $backOffice->getKey())
            ->assertTableColumnStateSet('areas', ['Loket', 'Back Office', 'Pengawasan'], (string) Role::findByName('admin')->getKey())
            ->assertSee(UserResource::getUrl('index', ['tableFilters[roles][values][0]' => $backOffice->id]), false);
    }

    public function test_permission_matrix_lists_roles_against_permissions(): void
    {
        Livewire::test(ListRoles::class)
            ->mountAction('matrix')
            ->assertSee('Peta izin per peran')
            ->assertSee('Back Office')
            ->assertSee('buka area back office')
            ->assertSee('Front Desk');
    }
}
