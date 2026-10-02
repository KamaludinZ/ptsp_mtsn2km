<?php

namespace Tests\Feature;

use App\Filament\Pages\System\ProcessorRoleList;
use App\Models\Service;
use App\Models\User;
use App\Support\ProcessorRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Daftar peran pemroses naskah. */
class ProcessorRoleListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::role('admin')->firstOrFail();
    }

    public function test_admin_sees_all_six_processor_roles(): void
    {
        $this->actingAs($this->admin())
            ->get(ProcessorRoleList::getUrl())
            ->assertOk()
            ->assertSeeInOrder(['Waka Humas', 'Waka Kesiswaan', 'Waka Kurikulum', 'Waka Sarpras', 'Tata Usaha', 'Penjamin Mutu']);
    }

    public function test_overview_counts_holders_and_receiving_services(): void
    {
        $role = Role::findOrCreate('waka_sarpras', 'web');
        $waka = User::where('email', 'waka.kesiswaan@mtsn2malang.sch.id')->firstOrFail();
        $waka->assignRole($role);
        Service::query()->firstOrFail()->update(['disposition_roles' => ['waka_sarpras', 'tata_usaha']]);

        $rows = collect(ProcessorRoles::overview())->keyBy('name');

        $this->assertSame(['Waka Kesiswaan'], $rows['waka_sarpras']['holders']->pluck('name')->all());
        $this->assertSame(1, $rows['waka_sarpras']['services']);
        $this->assertSame(1, $rows['tata_usaha']['services']);
        $this->assertNull($rows['waka_humas']['role']);

        $this->actingAs($this->admin())
            ->get(ProcessorRoleList::getUrl())
            ->assertSee('Waka Kesiswaan')
            ->assertSee('Peran belum dibuat');
    }

    public function test_back_office_cannot_open_the_page(): void
    {
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail())
            ->get(ProcessorRoleList::getUrl())
            ->assertForbidden();
    }

    public function test_admin_assigns_staff_to_a_processor_role(): void
    {
        $this->actingAs($this->admin());
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $waka = User::where('email', 'waka.kesiswaan@mtsn2malang.sch.id')->firstOrFail();

        Livewire::test(ProcessorRoleList::class)
            ->assertSee('Atur pemegang')
            ->callAction('assign', ['users' => [$waka->id, $staff->id]], ['role' => 'waka_kesiswaan'])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertTrue($waka->fresh()->hasRole('waka_kesiswaan'));
        $this->assertTrue($staff->fresh()->hasRole('back_office'), 'back office role is kept');

        Livewire::test(ProcessorRoleList::class)
            ->mountAction('assign', ['role' => 'waka_kesiswaan'])
            ->assertActionDataSet(['users' => collect([$waka->id, $staff->id])->sort()->values()->all()])
            ->setActionData(['users' => [$waka->id]])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertFalse($staff->fresh()->hasRole('waka_kesiswaan'));
    }

    public function test_only_back_office_staff_can_be_assigned(): void
    {
        $this->actingAs($this->admin());
        $frontDesk = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();

        Livewire::test(ProcessorRoleList::class)
            ->callAction('assign', ['users' => [$frontDesk->id]], ['role' => 'waka_humas'])
            ->assertHasActionErrors();

        $this->assertFalse($frontDesk->fresh()->hasRole('waka_humas'));
    }
}
