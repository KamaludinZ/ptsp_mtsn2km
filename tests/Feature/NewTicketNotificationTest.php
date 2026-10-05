<?php

namespace Tests\Feature;

use App\Events\TicketSubmitted;
use App\Models\Service;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Notifikasi "permohonan baru masuk". */
class NewTicketNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        \App\Models\Notification::query()->delete(); // start from no notifications
    }

    private function open(Service $service, ?User $actor = null, string $mode = 'online')
    {
        return app(TicketService::class)->open($service, $this->applicant, $actor ?? $this->applicant, $mode, 'Permohonan uji.');
    }

    public function test_back_office_and_disposers_are_notified_once(): void
    {
        $service = Service::requestable('umum', 'online')->where('approval_required', true)->firstOrFail();
        $ticket = $this->open($service);

        $officers = User::role('back_office')->where('is_active', true)->get();
        $this->assertNotEmpty($officers);
        foreach ($officers as $officer) {
            $this->assertSame(1, $officer->notifications()->count(), $officer->email);
            $this->assertSame($ticket->id, $officer->notifications()->first()->ticket_id);
        }

        $disposers = app(\App\Services\DispositionAuthority::class)->disposers($ticket);
        $this->assertNotEmpty($disposers);
        $this->assertSame('Permohonan baru menunggu disposisi', $disposers->first()->notifications()->first()->title());
        $this->assertSame(0, $this->applicant->notifications()->count());
    }

    public function test_services_without_disposition_only_notify_the_back_office(): void
    {
        $service = Service::requestable('umum', 'online')->firstOrFail();
        \App\Support\ServiceDisposition::apply($service, 'none');
        $this->open($service->fresh());

        $this->assertSame(0, User::role('kepala_sekolah')->first()->notifications()->count());
        $this->assertSame('Permohonan baru masuk', User::role('back_office')->first()->notifications()->first()->title());
    }

    public function test_the_counter_officer_is_not_notified_of_their_own_registration(): void
    {
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $counter->assignRole('back_office');

        $this->open(Service::where('is_active', true)->firstOrFail(), $counter, 'offline');

        $this->assertSame(0, $counter->notifications()->count());
    }

    public function test_the_event_fires_only_after_the_request_is_saved(): void
    {
        Event::fake([TicketSubmitted::class]);

        $ticket = $this->open(Service::requestable('umum', 'online')->firstOrFail());

        Event::assertDispatched(TicketSubmitted::class, fn (TicketSubmitted $e) => $e->ticket->is($ticket) && $e->actor?->is($this->applicant));
    }
}
