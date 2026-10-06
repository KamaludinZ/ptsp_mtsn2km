<?php

namespace Tests\Feature;

use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Widgets\Leadership\ActiveRoleDecisionPanel;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Panel keputusan peran aktif di Antrean Disposisi. */
class ActiveRoleDecisionPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function leaderAlsoAtTheCounter(string $active): User
    {
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);
        $user->forceFill(['active_role_id' => Role::findByName($active, 'web')->id])->save();

        return $user->fresh();
    }

    private function awaiting(): int
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        return Ticket::awaitingApproval()->count();
    }

    public function test_leader_working_at_the_counter_is_told_where_the_queue_went(): void
    {
        $count = $this->awaiting();
        $this->actingAs($this->leaderAlsoAtTheCounter('front_desk'));

        Livewire::test(ActiveRoleDecisionPanel::class)
            ->assertSee('Memutuskan sebagai: Front Desk')
            ->assertSee('Peran aktif Anda bukan peran pimpinan')
            ->assertSeeInOrder(['Menunggu keputusan di peran ini', '0 permohonan', 'Menunggu peran Kepala Tata Usaha'])
            ->assertSee('Putuskan sebagai Kepala Tata Usaha');

        $this->assertGreaterThan(0, $count);
    }

    public function test_in_the_leader_role_the_queue_is_theirs(): void
    {
        $this->awaiting();
        $this->actingAs($this->leaderAlsoAtTheCounter('kepala_tu'));

        Livewire::test(ActiveRoleDecisionPanel::class)
            ->assertSee('Memutuskan sebagai: Kepala Tata Usaha')
            ->assertDontSee('Peran aktif Anda bukan peran pimpinan')
            ->assertDontSee('Putuskan sebagai');

        $this->assertSame('Diputuskan sebagai Kepala Tata Usaha.', Approvals::decidingAs());
    }

    public function test_approvals_page_shows_the_panel_for_multi_role_leaders_only(): void
    {
        $this->actingAs($this->leaderAlsoAtTheCounter('kepala_tu'))
            ->get(Approvals::getUrl())->assertOk()->assertSee('Memutuskan sebagai: Kepala Tata Usaha');

        $this->flushSession();
        $this->actingAs(User::role('kepala_sekolah')->firstOrFail())
            ->get(Approvals::getUrl())->assertOk()->assertDontSee('Memutuskan sebagai');
    }
}
