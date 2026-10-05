<?php

namespace Tests\Feature;

use App\Filament\Pages\Notifications;
use App\Filament\Portal\Pages\Notifications as PortalNotifications;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Halaman Notifikasi: in-app notifications of the signed-in user. */
class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function notify(User $user, string $title, string $url = '/cp'): void
    {
        Notification::make()->title($title)->body('Isi ' . $title)
            ->actions([Action::make('open')->url($url)])
            ->sendToDatabase($user);
    }

    public function test_demo_data_seeds_notifications_for_staff_and_applicants(): void
    {
        $this->assertGreaterThan(0, $this->staff->notifications()->count());
        $this->assertGreaterThan(0, $this->staff->unreadNotifications()->count());
        $this->assertTrue(User::whereHas('notifications')->get()->contains(fn (User $u) => ! $u->isStaff()));
    }

    public function test_staff_read_filter_and_open_their_notifications(): void
    {
        $this->staff->notifications()->delete();
        $this->notify($this->staff, 'Permohonan baru masuk', '/cp/tiket');
        $this->notify($this->staff, 'Disposisi untuk unit Anda');
        $this->notify(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail(), 'Milik orang lain');
        $this->actingAs($this->staff);

        $this->get(Notifications::getUrl())->assertOk()->assertSee('2 notifikasi belum dibaca.');
        $this->assertSame('2', Notifications::getNavigationBadge());

        $first = $this->staff->notifications()->where('data->title', 'Permohonan baru masuk')->firstOrFail();
        Livewire::test(Notifications::class)
            ->assertCanSeeTableRecords($this->staff->notifications)
            ->assertDontSee('Milik orang lain')
            ->callTableAction('open', $first)
            ->assertRedirect('/cp/tiket');
        $this->assertNotNull($first->fresh()->read_at);

        Livewire::test(Notifications::class)
            ->set('show', 'belum-dibaca')
            ->assertCanNotSeeTableRecords([$first])
            ->callTableAction('markAllRead')
            ->assertNotified('Semua notifikasi ditandai dibaca')
            ->assertSee('Tidak ada notifikasi belum dibaca');
        $this->assertSame(0, $this->staff->unreadNotifications()->count());

        Livewire::test(Notifications::class)->callTableAction('toggleRead', $first);
        $this->assertNull($first->fresh()->read_at);
        Livewire::test(Notifications::class)->callTableAction('delete', $first);
        $this->assertModelMissing($first);
    }

    public function test_applicants_have_their_own_notification_page(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $applicant->notifications()->delete();
        $this->notify($applicant, 'Permohonan Anda diperbarui', '/portal');
        $this->actingAs($applicant);
        Filament::setCurrentPanel(Filament::getPanel('portal'));

        $this->get(PortalNotifications::getUrl(panel: 'portal'))->assertOk()->assertSee('Permohonan Anda diperbarui');

        $applicant->notifications()->delete();
        Livewire::test(PortalNotifications::class)->assertSee('Belum ada notifikasi');
    }

    public function test_the_bell_is_enabled_on_both_panels(): void
    {
        $this->assertTrue(Filament::getPanel('admin')->hasDatabaseNotifications());
        $this->assertTrue(Filament::getPanel('portal')->hasDatabaseNotifications());
    }

    public function test_public_navbar_bell_shows_the_unread_count(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $applicant->notifications()->delete();
        $this->notify($applicant, 'Satu');
        $this->notify($applicant, 'Dua');
        $this->actingAs($applicant);

        $this->get(route('home'))->assertOk()
            ->assertSee('Notifikasi, 2 belum dibaca')
            ->assertSee('data-unread="2"', false)
            ->assertSee(PortalNotifications::getUrl(panel: 'portal'), false);

        $applicant->unreadNotifications()->update(['read_at' => now()]);
        $this->get(route('home'))->assertSee('Notifikasi, tidak ada yang belum dibaca')->assertDontSee('data-unread', false);

        $this->actingAs($this->staff)->get(route('home'))->assertSee(Notifications::getUrl(panel: 'admin'), false);

        auth()->logout();
        $this->get(route('home'))->assertDontSee('notification-bell', false);
    }

    public function test_badge_caps_at_99(): void
    {
        $this->staff->notifications()->delete();
        foreach (range(1, 100) as $i) {
            $this->notify($this->staff, "N{$i}");
        }
        $this->actingAs($this->staff);

        $this->get(route('home'))->assertSee('>99+<', false);
        $this->assertSame('100', Notifications::getNavigationBadge());
    }

    public function test_clicking_a_notification_opens_the_request_where_the_reader_works(): void
    {
        $ticket = \App\Models\Ticket::factory()->create(['user_id' => User::where('email', 'budi.santoso@email.com')->value('id')]);
        $applicant = $ticket->user;
        $this->staff->notifications()->delete();
        $applicant->notifications()->delete();

        \App\Support\TicketNotification::send($this->staff, $ticket, 'Permohonan baru masuk');
        \App\Support\TicketNotification::send($applicant, $ticket, 'Permohonan Anda diperbarui');

        $staffUrl = \App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin');
        $portalUrl = \App\Filament\Portal\Resources\TicketResource::getUrl('view', ['record' => $ticket], panel: 'portal');

        $this->actingAs($this->staff);
        $note = $this->staff->notifications()->sole();
        $this->assertSame($ticket->ticket_number, $note->data['viewData']['ticket_number']);
        Livewire::test(Notifications::class)->callTableAction('open', $note)->assertRedirect($staffUrl);
        $this->assertNotNull($note->fresh()->read_at);

        $this->actingAs($applicant);
        Filament::setCurrentPanel(Filament::getPanel('portal'));
        Livewire::test(PortalNotifications::class)->callTableAction('open', $applicant->notifications()->sole())->assertRedirect($portalUrl);
    }

    public function test_old_notifications_without_a_link_resolve_by_ticket_number_and_never_leak(): void
    {
        $ticket = \App\Models\Ticket::factory()->create();
        $stranger = User::factory()->create();
        $stranger->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'legacy',
            'data' => ['title' => 'Lama', 'ticket_number' => $ticket->ticket_number],
        ]);
        $this->staff->notifications()->delete();
        $this->staff->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'legacy',
            'data' => ['title' => 'Lama', 'ticket_number' => $ticket->ticket_number],
        ]);

        $this->assertSame(\App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin'),
            \App\Support\TicketNotification::urlOf($this->staff->notifications()->sole(), $this->staff));
        // Someone who neither handles nor owns the request gets no link.
        $this->assertNull(\App\Support\TicketNotification::urlOf($stranger->notifications()->sole(), $stranger));
    }
}
