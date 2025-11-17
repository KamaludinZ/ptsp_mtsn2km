<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $senderId;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url');
        $this->apiToken = config('whatsapp.api_token');
        $this->senderId = config('whatsapp.sender_id');
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
