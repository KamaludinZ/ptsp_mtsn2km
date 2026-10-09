<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Daftar pengguna (admin). */
class UserListTest extends TestCase
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

    public function test_tabs_split_staff_applicants_and_deactivated_accounts(): void
    {
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $inactive = User::factory()->create(['name' => 'Akun Lama', 'is_active' => false]);

        $this->get(UserResource::getUrl())->assertOk()->assertSee('Akun petugas dan pemohon');

        Livewire::test(ListUsers::class)
            ->set('activeTab', 'petugas')->assertCanSeeTableRecords([$staff])->assertCanNotSeeTableRecords([$applicant])
            ->set('activeTab', 'pemohon')->assertCanSeeTableRecords([$applicant])->assertCanNotSeeTableRecords([$staff])
            ->set('activeTab', 'nonaktif')->assertCanSeeTableRecords([$inactive])->assertCanNotSeeTableRecords([$staff, $applicant]);
    }

    public function test_import_action_opens_upload_modal_and_requires_a_file(): void
    {
        $this->get(UserResource::getUrl())->assertOk()->assertSee('Unggah berkas akun');

        Livewire::test(ListUsers::class)
            ->assertActionExists('import')
            ->mountAction('import')
            ->assertSee('Import akun dari berkas')
            ->callMountedAction()
            ->assertHasActionErrors(['file' => 'required']);
    }

    public function test_last_sign_in_and_role_filters(): void
    {
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $never = User::factory()->create(['name' => 'Belum Masuk']);
        $staff->forceFill(['last_login_at' => now()->subHours(3)])->save();
        $never->forceFill(['last_login_at' => null])->save();

        Livewire::test(ListUsers::class)
            ->searchTable('staff1')
            ->assertTableColumnStateSet('last_login_at', $staff->fresh()->last_login_at, (string) $staff->getKey())
            ->assertSee('3 jam yang lalu');

        Livewire::test(ListUsers::class)
            ->filterTable('never_logged_in', true)
            ->assertCanSeeTableRecords([$never])
            ->assertCanNotSeeTableRecords([$staff])
            ->resetTableFilters()
            ->filterTable('roles', [\Spatie\Permission\Models\Role::findByName('back_office')->id])
            ->assertCanSeeTableRecords([$staff])
            ->assertCanNotSeeTableRecords([$never]);
    }
}
