<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** A one-off WhatsApp message (not tied to a template), sent outside the web request. */
class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public string $recipient, public string $message)
    {
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        $whatsApp->sendMessage($this->recipient, $this->message);
    }
}
