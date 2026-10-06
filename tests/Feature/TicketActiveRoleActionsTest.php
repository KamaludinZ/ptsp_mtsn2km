<?php

namespace Tests\Feature;

use App\Filament\Resources\TicketResource;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Resources\TicketResource\Widgets\ActiveRoleActions;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Panel "Aksi untuk peran aktif" di halaman tiket. */
class TicketActiveRoleActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function multiRole(): User
    {
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);

        return $user->fresh();
    }

    public function test_ticket_page_shows_what_the_active_role_may_do(): void
    {
        $ticket = Ticket::query()->firstOrFail();

        $this->actingAs($this->multiRole())
            ->withSession([ActiveRoles::SESSION_KEY => 'front_desk'])
            ->get(TicketResource::getUrl('view', ['record' => $ticket]))
            ->assertOk()
            ->assertSee('Aksi untuk peran aktif: Front Desk')
            ->assertSee('data-ticket-action="handOver" data-allowed="ya"', false)
            ->assertSee('data-ticket-action="approve" data-allowed="tidak"', false)
            // Offered a switch to a role the user holds; nothing for admin-only delete.
            ->assertSee('Ganti ke Kepala Tata Usaha')
            ->assertSee(route('peran-aktif.ganti'), false);
    }

    public function test_switch_buttons_only_offer_roles_the_user_holds(): void
    {
        $user = $this->multiRole();
        $this->actingAs($user);
        $user->forceFill(['active_role_id' => \Spatie\Permission\Models\Role::findByName('kepala_tu', 'web')->id])->save();

        Livewire::test(ActiveRoleActions::class, ['record' => Ticket::query()->firstOrFail()])
            ->assertSee('Aksi untuk peran aktif: Kepala Tata Usaha')
            ->assertSeeHtml('data-ticket-action="approve" data-allowed="ya"')
            ->assertSeeHtml('data-ticket-action="delete" data-allowed="tidak"')
            ->assertDontSee('Ganti ke Administrator');
    }

    private function awaitingTicket(): Ticket
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        return $ticket->fresh();
    }

    private function actAs(User $user, string $role): void
    {
        $user->forceFill(['active_role_id' => \Spatie\Permission\Models\Role::findByName($role, 'web')->id])->save();
        $this->actingAs($user->fresh());
        // The context the middleware would set for this role on the next request.
        session([ActiveRoles::SESSION_KEY => $role]);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public function test_decision_buttons_are_hidden_outside_the_leader_role(): void
    {
        $ticket = $this->awaitingTicket();
        $user = $this->multiRole();

        $this->actAs($user, 'front_desk');
        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->assertActionHidden('approve')
            ->assertActionHidden('reject')
            ->assertActionVisible('processLocked')->assertActionDisabled('processLocked');

        $this->actAs($user, 'kepala_tu');
        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->assertActionVisible('approve')->assertActionEnabled('approve')
            ->assertActionEnabled('reject')
            ->assertActionHidden('processLocked');
    }

    public function test_leadership_menus_and_widgets_follow_the_active_role(): void
    {
        $user = $this->multiRole();

        $this->actAs($user, 'front_desk');
        $this->assertFalse(\App\Filament\Pages\Leadership\Approvals::shouldRegisterNavigation());
        $this->assertFalse(\App\Filament\Pages\Leadership\DispositionHistory::shouldRegisterNavigation());
        $this->assertFalse(\App\Filament\Widgets\Leadership\PendingApprovals::canView());
        $this->assertFalse(\App\Filament\Widgets\Leadership\LeadershipStats::canView());
        // Still reachable by link, to switch roles from there.
        $this->assertTrue(\App\Filament\Pages\Leadership\Approvals::canAccess());
        $this->get(\App\Filament\Pages\Dashboard::getUrl())->assertOk()->assertDontSee('Disposisi Masuk');

        // Filament mounts the menu once per app instance, so the leader state is checked directly.
        $this->actAs($user, 'kepala_tu');
        $this->assertTrue(\App\Filament\Pages\Leadership\Approvals::shouldRegisterNavigation());
        $this->assertSame('Pimpinan', \App\Filament\Pages\Leadership\DispositionHistory::getNavigationGroup());
        $this->assertTrue(\App\Filament\Widgets\Leadership\PendingApprovals::canView());
    }

    public function test_single_role_staff_see_no_locked_buttons(): void
    {
        $ticket = $this->awaitingTicket();
        $this->actingAs(User::role('front_desk')->firstOrFail());

        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->assertActionHidden('approve')
            ->assertActionHidden('processLocked');
    }

    public function test_single_role_staff_do_not_get_the_panel(): void
    {
        $this->actingAs(User::role('back_office')->firstOrFail());

        $this->assertFalse(ActiveRoleActions::canView());
    }
}
