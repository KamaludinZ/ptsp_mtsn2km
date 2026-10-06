<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** TicketPolicy: wewenang mengikuti peran aktif, bukan semua peran yang dipegang. */
class ActiveRolePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        RoleAccess::sync();
    }

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user->fresh();
    }

    /** A ticket waiting for the default leadership (kepala sekolah / kepala TU / admin). */
    private function awaitingTicket(): Ticket
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true])->save();

        return $ticket->fresh();
    }

    /** Act in a role the way the middleware sets it for a request. */
    private function actIn(User $user, ?string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public function test_leader_decides_only_while_acting_as_leader(): void
    {
        $ticket = $this->awaitingTicket();
        $user = $this->staff('kepala_tu', 'front_desk');

        $this->actIn($user, 'kepala_tu');
        $this->assertTrue($user->can('approve', $ticket));

        $this->actIn($user, 'front_desk');
        $this->assertFalse($user->can('approve', $ticket));
    }

    public function test_multi_role_staff_without_an_active_role_may_not_decide(): void
    {
        $ticket = $this->awaitingTicket();
        $user = $this->staff('kepala_tu', 'front_desk');

        $this->actIn($user, null);
        $this->assertFalse($user->can('approve', $ticket));

        // Still a leader to be told about new requests (ownership, not context).
        $this->assertTrue(app(DispositionAuthority::class)->canDispose($user, $ticket));
    }

    public function test_single_role_leaders_keep_their_authority(): void
    {
        $ticket = $this->awaitingTicket();
        $leader = $this->staff('kepala_sekolah');

        $this->actingAs($leader);
        $this->assertTrue($leader->can('approve', $ticket));
    }

    public function test_leaders_named_on_the_service_decide_in_any_role(): void
    {
        $ticket = $this->awaitingTicket();
        $user = $this->staff('front_desk', 'back_office');
        $ticket->service->forceFill(['approval_roles' => [], 'approval_users' => [$user->id]])->save();

        $this->actIn($user, 'front_desk');
        $this->assertTrue($user->can('approve', $ticket->fresh()));
    }

    public function test_processing_and_hand_over_follow_the_active_role(): void
    {
        $ticket = Ticket::query()->firstOrFail();
        $user = $this->staff('front_desk', 'back_office');

        $this->actIn($user, 'back_office');
        $this->assertTrue($user->can('update', $ticket));
        $this->assertFalse($user->can('handOver', $ticket));

        $this->actIn($user, 'front_desk');
        $this->assertFalse($user->can('update', $ticket));
        $this->assertTrue($user->can('handOver', $ticket));
    }

    public function test_disposition_records_the_role_the_leader_acted_in(): void
    {
        $ticket = $this->awaitingTicket();
        $ticket->forceFill(['status' => 'verified', 'approval_status' => 'pending'])->save();
        $user = $this->staff('admin', 'kepala_tu');

        $this->actIn($user, 'kepala_tu');
        $log = app(\App\Services\TicketService::class)->decide($ticket->fresh(), false, $user, null, 'Berkas belum lengkap.');

        $this->assertSame('kepala_tu', $log->role);
    }
}
