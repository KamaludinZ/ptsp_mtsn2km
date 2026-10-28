<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Policies\PersuratanMasterPolicy;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** PersuratanMasterPolicy: who may read, change and delete Jenis SK (and the other master lists). */
class JenisSkPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PersuratanMaster $jenis;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        RoleAccess::sync();
        $this->jenis = PersuratanMaster::where('type', 'jenis_sk')->orderBy('sort')->firstOrFail();
    }

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user->fresh();
    }

    /** Act in a role the way the middleware sets it for a request. */
    private function actIn(User $user, ?string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    /** @return array<string, bool> */
    private function abilities(User $user): array
    {
        $gate = Gate::forUser($user);

        return [
            'viewAny' => $gate->allows('viewAny', PersuratanMaster::class),
            'view' => $gate->allows('view', $this->jenis),
            'create' => $gate->allows('create', PersuratanMaster::class),
            'update' => $gate->allows('update', $this->jenis),
            'delete' => $gate->allows('delete', $this->jenis),
            'deleteAny' => $gate->allows('deleteAny', PersuratanMaster::class),
        ];
    }

    public function test_the_policy_is_registered_for_master_persuratan(): void
    {
        $this->assertInstanceOf(PersuratanMasterPolicy::class, Gate::getPolicyFor(PersuratanMaster::class));
    }

    public function test_back_office_reads_adds_and_edits_but_does_not_delete(): void
    {
        $user = $this->staff('back_office');
        $this->actIn($user, 'back_office');

        $this->assertSame(['viewAny' => true, 'view' => true, 'create' => true, 'update' => true, 'delete' => false, 'deleteAny' => false], $this->abilities($user));
    }

    public function test_administrator_may_do_everything(): void
    {
        $user = $this->staff('admin');
        $this->actIn($user, 'admin');

        $this->assertSame(['viewAny' => true, 'view' => true, 'create' => true, 'update' => true, 'delete' => true, 'deleteAny' => true], $this->abilities($user));
    }

    public function test_front_desk_and_applicants_get_nothing(): void
    {
        $frontDesk = $this->staff('front_desk');
        $this->actIn($frontDesk, 'front_desk');
        $this->assertNotContains(true, $this->abilities($frontDesk));

        $applicant = User::factory()->create();
        $this->actIn($applicant, null);
        $this->assertNotContains(true, $this->abilities($applicant));
    }

    public function test_deleting_follows_the_active_role_not_every_role_held(): void
    {
        $user = $this->staff('admin', 'back_office');

        $this->actIn($user, 'back_office');
        $this->assertFalse(Gate::forUser($user)->allows('delete', $this->jenis));
        $this->assertTrue(Gate::forUser($user)->allows('update', $this->jenis));

        $this->actIn($user, 'admin');
        $this->assertTrue(Gate::forUser($user)->allows('delete', $this->jenis));
    }

    public function test_deactivated_accounts_are_refused_even_as_admin(): void
    {
        $user = $this->staff('admin');
        $user->forceFill(['is_active' => false])->save();
        $this->actIn($user, 'admin');

        $this->assertNotContains(true, $this->abilities($user));
    }

    public function test_master_persuratan_tab_and_api_follow_the_policy(): void
    {
        $user = $this->staff('back_office');
        $this->actIn($user, 'back_office');

        $this->assertTrue(PersuratanMasterResource::canViewAny());
        $this->assertTrue(PersuratanMasterResource::canEdit($this->jenis));
        $this->assertFalse(PersuratanMasterResource::canDelete($this->jenis));
        $this->assertFalse(PersuratanMasterResource::canForceDelete($this->jenis));
        $this->assertFalse(PersuratanMasterResource::canDeleteAny());

        Sanctum::actingAs($user, ['*']);
        $this->getJson('/api/persuratan/jenis-sk')->assertOk();
        $this->putJson("/api/persuratan/jenis-sk/{$this->jenis->id}", ['urutan' => 3])->assertOk();
        $this->deleteJson("/api/persuratan/jenis-sk/{$this->jenis->id}")->assertForbidden();
    }
}
