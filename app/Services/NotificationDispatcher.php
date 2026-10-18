<?php

namespace App\Services;

use App\Filament\Resources\TicketResource;
use App\Jobs\DeliverNotification;
use App\Mail\TemplateNotificationMail;
use App\Models\NotificationSetting;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\Ticket;
use App\Models\User;
use App\Support\IntegrationRuntime;
use App\Support\NotificationTemplates;
use App\Support\TicketLabels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends a notification by its templates: on each channel that is switched
 * on and whose template is active, the {placeholders} are filled from the
 * ticket (or complaint) and the message goes out. A failing gateway is logged and never
 * breaks the service flow that triggered it. Pengaduan, saran & WBS go out on
 * their own line's gateways, everything else on the pelayanan line's
 * (NotificationTemplates::line()).
 */
class NotificationDispatcher
{
    /**
     * @param  array<string, string>  $extra  more placeholder values (e.g. catatan)
     * @return array<int, string> channels the message went out on
     */
    /** Silences every notification, e.g. while demo data is seeded. */
    public static bool $muted = false;

    public function send(string $event, Ticket $ticket, ?User $recipient = null, array $extra = []): array
    {
        $recipient ??= $ticket->user;
        if (static::$muted || ! $recipient) {
            return [];
        }

        return $this->sendTo($event, $this->values($ticket, $recipient, $extra), $recipient->email, $recipient->whatsapp_number, [
            'ticket_id' => $ticket->id,
            'user_id' => $recipient->id,
        ]);
    }

    /**
     * Send $event to an address that need not belong to an account (e.g. the
     * reporter of a complaint), with placeholder values already filled.
     *
     * @param  array{ticket_id?: ?int, complaint_id?: ?int, user_id?: ?int}  $context  what the riwayat notifikasi links to
     * @return array<int, string> channels the message went out (or was queued) on
     */
    public function sendTo(string $event, array $values, ?string $email, ?string $whatsapp, array $context = []): array
    {
        if (static::$muted) {
            return [];
        }

        $channels = [];

        foreach (NotificationTemplate::where('key', $event)->where('is_active', true)->get() as $template) {
            if (! NotificationSetting::forEvent($template->channel, $event)->is_enabled) {
                continue;
            }

            $to = $template->channel === 'email' ? $email : $whatsapp;
            if (blank($to)) {
                continue;
            }

            $subject = $template->subject ? NotificationTemplates::render($template->subject, $values) : null;
            $body = NotificationTemplates::render($template->body, $values);

            $delivery = new NotificationDelivery([
                'event' => $event,
                'channel' => $template->channel,
                'ticket_id' => $context['ticket_id'] ?? null,
                'complaint_id' => $context['complaint_id'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'recipient' => $to,
                'subject' => $template->channel === 'email' ? ($subject ?? NotificationTemplates::EVENTS[$event] ?? $event) : null,
                'body' => $body,
            ]);

            // Kirim lewat antrean (DeliverNotification): gateway yang lambat
            // tidak membuat pengguna menunggu. Dengan QUEUE_CONNECTION=sync
            // pesan tetap terkirim saat itu juga.
            $delivery->fill(['status' => 'queued', 'attempts' => 0])->save();
            DeliverNotification::dispatch($delivery->id)->afterCommit();

            if ($delivery->refresh()->status !== 'failed') {
                $channels[] = $template->channel;
            }
        }

        return $channels;
    }

    /**
     * Send one stored message and record the outcome in the riwayat
     * notifikasi (also used to resend a failed one).
     */
    public function deliver(NotificationDelivery $delivery): bool
    {
        $error = null;
        $line = NotificationTemplates::line($delivery->event);
        try {
            $delivered = $delivery->channel === 'email'
                ? $this->email($delivery->recipient, (string) $delivery->subject, $delivery->body, $delivery->event, $line)
                : (bool) app(WhatsAppService::class, ['line' => $line])->sendMessage($delivery->recipient, $delivery->body);
            $error = $delivered ? null : 'Layanan WhatsApp menolak pesan.';
        } catch (Throwable $e) {
            Log::warning("Notification {$delivery->event} via {$delivery->channel} failed: " . $e->getMessage(), ['ticket' => $delivery->ticket_id, 'complaint' => $delivery->complaint_id]);
            $delivered = false;
            $error = mb_strimwidth($e->getMessage(), 0, 500, '…');
        }

        $delivery->fill([
            'status' => $delivered ? 'sent' : 'failed',
            'error' => $error,
            'attempts' => $delivery->attempts + 1,
        ])->save();

        return $delivered;
    }

    /** Placeholder values for a ticket and its recipient. */
    public function values(Ticket $ticket, User $recipient, array $extra = []): array
    {
        return array_merge([
            'nama' => $recipient->name,
            'nomor_tiket' => $ticket->ticket_number,
            'layanan' => $ticket->service?->name ?? '',
            'status' => TicketLabels::status($ticket->status),
            'catatan' => '',
            'tautan' => $recipient->isStaff()
                ? TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin')
                : route('onlineportal.track.ticket.form'),
            'instansi' => app_brand_name(),
        ], $extra);
    }

    private function email(string $to, string $subject, string $body, string $event, string $line): bool
    {
        Mail::mailer(IntegrationRuntime::mailer($line))->to($to)->send(new TemplateNotificationMail($subject, $body, $event));

        return true;
    }
}
