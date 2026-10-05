<?php

namespace App\Services;

use App\Filament\Resources\TicketResource;
use App\Mail\TemplateNotificationMail;
use App\Models\NotificationSetting;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\Ticket;
use App\Models\User;
use App\Support\NotificationTemplates;
use App\Support\TicketLabels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends a notification by its templates: on each channel that is switched
 * on and whose template is active, the {placeholders} are filled from the
 * ticket and the message goes out. A failing gateway is logged and never
 * breaks the service flow that triggered it.
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

        $values = $this->values($ticket, $recipient, $extra);
        $channels = [];

        foreach (NotificationTemplate::where('key', $event)->where('is_active', true)->get() as $template) {
            if (! NotificationSetting::for($template->channel)->is_enabled) {
                continue;
            }

            $to = $template->channel === 'email' ? $recipient->email : $recipient->whatsapp_number;
            if (blank($to)) {
                continue;
            }

            $subject = $template->subject ? NotificationTemplates::render($template->subject, $values) : null;
            $body = NotificationTemplates::render($template->body, $values);

            $delivery = new NotificationDelivery([
                'event' => $event,
                'channel' => $template->channel,
                'ticket_id' => $ticket->id,
                'user_id' => $recipient->id,
                'recipient' => $to,
                'subject' => $template->channel === 'email' ? ($subject ?? NotificationTemplates::EVENTS[$event] ?? $event) : null,
                'body' => $body,
            ]);

            if ($this->deliver($delivery)) {
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
        try {
            $delivered = $delivery->channel === 'email'
                ? $this->email($delivery->recipient, (string) $delivery->subject, $delivery->body, $delivery->event)
                : (bool) app(WhatsAppService::class)->sendMessage($delivery->recipient, $delivery->body);
            $error = $delivered ? null : 'Layanan WhatsApp menolak pesan.';
        } catch (Throwable $e) {
            Log::warning("Notification {$delivery->event} via {$delivery->channel} failed: " . $e->getMessage(), ['ticket' => $delivery->ticket_id]);
            $delivered = false;
            $error = mb_strimwidth($e->getMessage(), 0, 500, '…');
        }

        $delivery->fill([
            'status' => $delivered ? 'sent' : 'failed',
            'error' => $error,
            'attempts' => $delivery->exists ? $delivery->attempts + 1 : 1,
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

    private function email(string $to, string $subject, string $body, string $event): bool
    {
        Mail::to($to)->send(new TemplateNotificationMail($subject, $body, $event));

        return true;
    }
}
