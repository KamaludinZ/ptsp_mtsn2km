<?php

namespace App\Jobs;

use App\Models\NotificationDelivery;
use App\Services\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Sends one stored notification (email/WhatsApp) outside the web request,
 * so a slow gateway never keeps applicants or staff waiting. A failed send
 * is tried again a few minutes later; the riwayat notifikasi keeps the
 * outcome of every attempt.
 */
class DeliverNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public function __construct(public int $deliveryId)
    {
    }

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $delivery = NotificationDelivery::find($this->deliveryId);
        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        if (! $dispatcher->deliver($delivery) && $this->attempts() < $this->tries) {
            $this->release(120 * $this->attempts());
        }
    }
}
