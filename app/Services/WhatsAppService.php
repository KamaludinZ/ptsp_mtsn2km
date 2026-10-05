<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Support\WhatsAppGateways;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $senderId;

    /** Gateway chosen on Integrasi Notifikasi (WhatsAppGateways::PROVIDERS). */
    protected ?string $provider = null;

    /** Whether the admin switched WhatsApp off on Integrasi Notifikasi. */
    protected bool $disabled = false;

    public function __construct()
    {
        // Settings saved on Integrasi Notifikasi win over .env, read at send time.
        $saved = NotificationSetting::where('channel', 'whatsapp')->first();
        $this->disabled = $saved !== null && ! $saved->is_enabled;

        $this->apiUrl = $saved?->value('api_url') ?: config('whatsapp.api_url');
        $this->apiToken = $saved?->value('api_token') ?: config('whatsapp.api_token');
        $this->senderId = $saved?->value('sender_id') ?: config('whatsapp.sender_id');
        $this->provider = $saved?->value('provider') ?: 'custom';
    }

    /**
     * Send a WhatsApp message.
     *
     * @param string $recipient The recipient's phone number (e.g., 6281234567890)
     * @param string $message The message content.
     * @return bool
     */
    public function sendMessage(string $recipient, string $message): bool
    {
        if ($this->disabled) {
            Log::info('WhatsApp notifications are switched off; message not sent.');

            return false;
        }

        if (WhatsAppGateways::missing($this->provider, $this->apiUrl, $this->apiToken, $this->senderId)) {
            Log::error('WhatsApp service is not configured. Please check Integrasi Notifikasi or your .env file.');
            return false;
        }

        try {
            $response = WhatsAppGateways::send($this->provider, $this->apiUrl, $this->apiToken, $this->senderId, $recipient, $message, 30);

            if (WhatsAppGateways::accepted($response)) {
                Log::info("WhatsApp message sent to {$recipient}.");
                return true;
            } else {
                Log::error("Failed to send WhatsApp message to {$recipient}. Response: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending WhatsApp message to {$recipient}: " . $e->getMessage());
            return false;
        }
    }
}
