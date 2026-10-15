<?php

namespace Tests\Feature;

use App\Mail\TemplateNotificationMail;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Notifications go out by their templates, only on active channels and templates. */
class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();

        $this->applicant = tap(User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@example.test', 'whatsapp_number' => '6281299990000']))->assignRole('umum');
        $service = Service::availableFor('umum')->firstOrFail();
        $this->ticket = app(TicketService::class)->open($service, $this->applicant, $this->applicant, 'online', 'Legalisir');
        Mail::fake(); // forget anything opening the ticket sent
    }

    private function enable(string $channel): void
    {
        NotificationSetting::store($channel, true, $channel === 'email'
            ? ['host' => 'smtp.test', 'port' => 25, 'from_address' => 'ptsp@test']
            : ['api_url' => 'https://wa.test/send', 'api_token' => 't', 'sender_id' => '628000']);
    }

    public function test_message_is_rendered_and_sent_on_enabled_channels(): void
    {
        $this->enable('email');
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response(['ok' => true])]);

        $channels = app(NotificationDispatcher::class)->send('ticket_created', $this->ticket);

        $this->assertSame(['email', 'whatsapp'], collect($channels)->sort()->values()->all());
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->hasTo('siti@example.test')
            && $mail->subjectLine === 'Permohonan ' . $this->ticket->ticket_number . ' diterima'
            && str_contains($mail->body, 'Yth. Siti Aminah'));
        Http::assertSent(fn ($request) => $request['number'] === '6281299990000' && str_contains($request['message'], $this->ticket->ticket_number));
    }

    public function test_nothing_is_sent_when_the_channel_or_template_is_off(): void
    {
        // Channels never switched on.
        $this->assertSame([], app(NotificationDispatcher::class)->send('ticket_created', $this->ticket));

        $this->enable('email');
        NotificationTemplate::where('key', 'ticket_created')->where('channel', 'email')->update(['is_active' => false]);
        $this->assertSame([], app(NotificationDispatcher::class)->send('ticket_created', $this->ticket));
        Mail::assertNothingSent();
    }

    public function test_a_failing_gateway_does_not_break_the_caller(): void
    {
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response('down', 500)]);

        $this->assertSame([], app(NotificationDispatcher::class)->send('ticket_created', $this->ticket));
    }

    public function test_extra_values_fill_their_placeholders(): void
    {
        $this->enable('email');

        app(NotificationDispatcher::class)->send('ticket_rejected', $this->ticket, null, ['catatan' => 'Berkas tidak terbaca']);

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => str_contains($mail->body, 'Alasan: Berkas tidak terbaca'));
    }

    public function test_service_events_send_their_notifications(): void
    {
        $this->enable('email');
        $service = app(TicketService::class);
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        $ticket = $service->open($this->ticket->service, $this->applicant, $this->applicant, 'online', 'Surat aktif');
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'ticket_created' && $mail->hasTo('siti@example.test'));

        $service->changeStatus($ticket->fresh(), 'rejected', 'Berkas tidak lengkap', $officer);
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'ticket_rejected' && str_contains($mail->body, 'Berkas tidak lengkap'));
    }

    public function test_disposition_notifies_the_receiving_units_staff(): void
    {
        $this->enable('email');
        $waka = tap(User::factory()->create(['email' => 'waka@example.test']))->assignRole('waka_kurikulum');
        $headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $this->ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);

        app(TicketService::class)->dispose($this->ticket->fresh(), $headmaster, 'acknowledged_by', ['waka_kurikulum'], 'Untuk ditindaklanjuti');

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_assigned' && $mail->hasTo('waka@example.test') && str_contains($mail->body, 'Untuk ditindaklanjuti'));
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'ticket_status_changed' && $mail->hasTo('siti@example.test'));
    }

    public function test_a_request_needing_disposition_reaches_the_headmaster_and_head_of_admin_with_steps(): void
    {
        $this->enable('email');
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response(['ok' => true])]);

        $headmaster = tap(User::role('kepala_sekolah')->firstOrFail())->update(['whatsapp_number' => '6281200000001']);
        $headOfAdmin = tap(User::factory()->create(['email' => 'ktu@example.test', 'whatsapp_number' => '6281200000002']))->assignRole('kepala_tu');
        $service = $this->ticket->service;
        \App\Support\ServiceDisposition::apply($service, 'kepsek_tu');

        $ticket = app(TicketService::class)->open($service->fresh(), $this->applicant, $this->applicant, 'online', 'Surat keterangan');

        foreach ([$headmaster, $headOfAdmin] as $leader) {
            Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested'
                && $mail->hasTo($leader->email)
                && str_contains($mail->subjectLine, $ticket->ticket_number)
                && str_contains($mail->body, 'Siti Aminah')
                && str_contains($mail->body, 'Cara mendisposisi')
                && str_contains($mail->body, '"Disposisikan"')
                && str_contains($mail->body, \App\Filament\Resources\TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin')));
            Http::assertSent(fn ($request) => $request['number'] === $leader->whatsapp_number && str_contains($request['message'], 'Cara mendisposisi'));
        }
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested' && $mail->hasTo($headmaster->email) && str_contains($mail->body, 'peran aktif di kanan atas adalah Kepala Madrasah.'));
        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested' && $mail->hasTo('ktu@example.test') && str_contains($mail->body, 'adalah Kepala Tata Usaha.'));
    }

    public function test_only_the_leader_of_the_disposition_mode_is_messaged(): void
    {
        $this->enable('email');
        $headOfAdmin = tap(User::factory()->create(['email' => 'ktu@example.test']))->assignRole('kepala_tu');
        $headmaster = User::role('kepala_sekolah')->firstOrFail();
        $service = $this->ticket->service;
        \App\Support\ServiceDisposition::apply($service, 'tu');

        app(TicketService::class)->open($service->fresh(), $this->applicant, $this->applicant, 'online', 'Legalisir');

        Mail::assertSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested' && $mail->hasTo('ktu@example.test'));
        Mail::assertNotSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested' && $mail->hasTo($headmaster->email));

        // No disposition, no message to the leaders.
        Mail::fake();
        \App\Support\ServiceDisposition::apply($service->fresh(), 'none');
        app(TicketService::class)->open($service->fresh(), $this->applicant, $this->applicant, 'online', 'Legalisir');
        Mail::assertNotSent(TemplateNotificationMail::class, fn ($mail) => $mail->event === 'disposition_requested');
    }

    public function test_a_rolled_back_change_sends_nothing(): void
    {
        $this->enable('email');

        try {
            DB::transaction(function () {
                app(TicketService::class)->open($this->ticket->service, $this->applicant, $this->applicant, 'online', 'Gagal');
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
        }

        Mail::assertNothingSent();
    }

    public function test_every_attempt_is_recorded_with_its_status(): void
    {
        $this->enable('email');
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response(['error' => 'nomor tidak terdaftar'], 500)]);

        app(NotificationDispatcher::class)->send('ticket_created', $this->ticket);

        $email = \App\Models\NotificationDelivery::where('channel', 'email')->sole();
        $this->assertSame(['ticket_created', 'sent', 'siti@example.test', $this->ticket->id, $this->applicant->id, 1],
            [$email->event, $email->status, $email->recipient, $email->ticket_id, $email->user_id, $email->attempts]);
        $this->assertStringContainsString($this->ticket->ticket_number, $email->body);

        $whatsapp = \App\Models\NotificationDelivery::where('channel', 'whatsapp')->sole();
        $this->assertSame(['failed', '6281299990000'], [$whatsapp->status, $whatsapp->recipient]);
        $this->assertNotNull($whatsapp->error);
    }

    public function test_admin_reviews_and_resends_failed_notifications(): void
    {
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::sequence()->push(['error' => 'down'], 500)->push(['ok' => true], 200)]);
        app(NotificationDispatcher::class)->send('ticket_created', $this->ticket);
        $failed = \App\Models\NotificationDelivery::sole();
        $this->assertSame('failed', $failed->status);

        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $page = \App\Filament\Resources\NotificationDeliveryResource\Pages\ListNotificationDeliveries::class;

        $this->get(\App\Filament\Resources\NotificationDeliveryResource::getUrl())->assertOk()
            ->assertSee('Riwayat Notifikasi')->assertSee('0 terkirim, 1 gagal');
        $this->assertSame('1', \App\Filament\Resources\NotificationDeliveryResource::getNavigationBadge());

        \Livewire\Livewire::test($page)
            ->set('activeTab', 'gagal')
            ->assertCanSeeTableRecords([$failed])
            ->assertTableActionVisible('resend', $failed)
            ->callTableAction('resend', $failed)
            ->assertNotified('Notifikasi terkirim');

        $failed->refresh();
        $this->assertSame(['sent', 2, null], [$failed->status, $failed->attempts, $failed->error]);

        \Livewire\Livewire::test($page)
            ->filterTable('channel', 'email')
            ->assertCanNotSeeTableRecords([$failed])
            ->resetTableFilters()
            ->assertTableActionHidden('resend', $failed);
    }

    public function test_notification_history_is_admin_only(): void
    {
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->get(\App\Filament\Resources\NotificationDeliveryResource::getUrl())->assertForbidden();
    }

    public function test_messages_wait_in_the_queue_instead_of_the_request(): void
    {
        $this->enable('email');
        $this->enable('whatsapp');
        \Illuminate\Support\Facades\Queue::fake();

        $channels = app(NotificationDispatcher::class)->send('ticket_created', $this->ticket);

        // Nothing is sent while the user waits: both messages are queued and recorded as such.
        $this->assertSame(['email', 'whatsapp'], collect($channels)->sort()->values()->all());
        Mail::assertNothingSent();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeliverNotification::class, 2);
        $this->assertSame(2, \App\Models\NotificationDelivery::where('status', 'queued')->where('attempts', 0)->count());
    }

    public function test_the_queue_worker_sends_and_retries_a_failed_message(): void
    {
        $this->enable('whatsapp');
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::sequence()->push(['error' => 'sibuk'], 503)->push(['ok' => true])]);
        $delivery = \App\Models\NotificationDelivery::create(['event' => 'ticket_created', 'channel' => 'whatsapp', 'ticket_id' => $this->ticket->id,
            'recipient' => '6281299990000', 'body' => 'Isi', 'status' => 'queued', 'attempts' => 0]);

        $job = new \App\Jobs\DeliverNotification($delivery->id);
        $job->withFakeQueueInteractions()->handle(app(NotificationDispatcher::class));
        $job->assertReleased();
        $this->assertSame(['failed', 1], [$delivery->fresh()->status, $delivery->fresh()->attempts]);

        (new \App\Jobs\DeliverNotification($delivery->id))->withFakeQueueInteractions()->handle(app(NotificationDispatcher::class));
        $this->assertSame(['sent', 2], [$delivery->fresh()->status, $delivery->fresh()->attempts]);
    }
}
