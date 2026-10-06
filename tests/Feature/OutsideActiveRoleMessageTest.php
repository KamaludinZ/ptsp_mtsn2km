<?php

namespace Tests\Feature;

use App\Exceptions\OutsideActiveRoleException;
use App\Exceptions\TicketActionException;
use App\Filament\Concerns\NotifiesActionResult;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Pesan penolakan aksi di luar wewenang peran aktif. */
class OutsideActiveRoleMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function awaitingTicket(): Ticket
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        return $ticket->fresh();
    }

    private function actIn(User $user, string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    private function decide(Ticket $ticket, User $user): TicketActionException
    {
        try {
            app(TicketService::class)->decide($ticket, false, $user, null, 'Berkas belum lengkap.');
        } catch (TicketActionException $e) {
            return $e;
        }
        $this->fail('The decision should have been refused.');
    }

    public function test_leader_in_another_role_is_told_which_role_to_activate(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);
        $this->actIn($user->fresh(), 'front_desk');

        $e = $this->decide($this->awaitingTicket(), $user->fresh());

        $this->assertInstanceOf(OutsideActiveRoleException::class, $e);
        $this->assertSame('Peran aktif Anda (Front Desk) tidak berwenang memutuskan permohonan ini. Aktifkan peran Kepala Tata Usaha untuk melanjutkan.', $e->getMessage());
        $this->assertSame(['front_desk', 'kepala_tu'], [$e->activeRole, $e->suggestedRole]);
    }

    public function test_staff_who_hold_no_leader_role_get_the_plain_refusal(): void
    {
        $user = User::role('front_desk')->firstOrFail();
        $this->actIn($user, 'front_desk');

        $e = $this->decide($this->awaitingTicket(), $user);

        $this->assertNotInstanceOf(OutsideActiveRoleException::class, $e);
        $this->assertSame('Anda tidak berwenang memutuskan permohonan ini.', $e->getMessage());
    }

    public function test_panels_show_the_refusal_with_a_switch_button(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);
        $this->actIn($user->fresh(), 'front_desk');
        $ticket = $this->awaitingTicket();

        $panel = new class
        {
            use NotifiesActionResult;

            public static function run(callable $callback): bool
            {
                return self::attempt($callback, 'Berhasil');
            }
        };
        $this->assertFalse($panel::run(fn () => app(TicketService::class)->decide($ticket, false, $user->fresh(), null, 'Tidak lengkap.')));

        $toast = collect(session('filament.notifications'))->last();
        $this->assertSame('Di luar wewenang peran aktif', $toast['title']);
        $this->assertStringContainsString('Aktifkan peran Kepala Tata Usaha', $toast['body']);
        $this->assertSame('Ganti ke Kepala Tata Usaha', $toast['actions'][0]['label']);
        $this->assertSame(ActiveRoleToast::UNDO_EVENT, $toast['actions'][0]['event']);
        $this->assertSame(['role' => 'kepala_tu'], $toast['actions'][0]['eventData']);
        $this->assertSame('pending', $ticket->fresh()->approval_status);
    }
}
