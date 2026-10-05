<?php

namespace App\Services;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $senderId;

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

        if (!$this->apiUrl || !$this->apiToken || !$this->senderId) {
            Log::error('WhatsApp service is not configured. Please check your .env file.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->post($this->apiUrl, [
                'sender' => $this->senderId,
                'number' => $recipient,
                'message' => $message,
            ]);

            if ($response->successful()) {
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
