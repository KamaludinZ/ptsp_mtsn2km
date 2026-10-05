<?php

namespace App\Services;

use App\Filament\Resources\TicketResource;
use App\Mail\TemplateNotificationMail;
use App\Models\NotificationSetting;
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
    public function send(string $event, Ticket $ticket, ?User $recipient = null, array $extra = []): array
    {
        $recipient ??= $ticket->user;
        if (! $recipient) {
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

            try {
                $delivered = $template->channel === 'email'
                    ? $this->email($to, $subject ?? NotificationTemplates::EVENTS[$event] ?? $event, $body, $event)
                    : app(WhatsAppService::class)->sendMessage($to, $body);
            } catch (Throwable $e) {
                Log::warning("Notification {$event} via {$template->channel} failed: " . $e->getMessage(), ['ticket' => $ticket->ticket_number]);
                $delivered = false;
            }

            if ($delivered) {
                $channels[] = $template->channel;
            }
        }

        return $channels;
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
