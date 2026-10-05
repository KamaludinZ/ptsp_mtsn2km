<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Support\WhatsAppGateways;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends through the gateways configured on the Integrasi Notifikasi page
 * (stored settings, falling back to .env for anything not saved yet).
 */
class NotificationGateway
{
    public function mailer(): Mailer
    {
        $email = NotificationSetting::for('email');

        $mailer = Mail::build([
            'transport' => 'smtp',
            'host' => $email->value('host', config('mail.mailers.smtp.host')),
            'port' => (int) $email->value('port', config('mail.mailers.smtp.port')),
            'encryption' => ($encryption = $email->value('encryption', config('mail.mailers.smtp.encryption'))) === 'none' ? null : $encryption,
            'username' => $email->value('username', config('mail.mailers.smtp.username')),
            'password' => $email->value('password', config('mail.mailers.smtp.password')),
            'timeout' => 15,
        ]);
        $mailer->alwaysFrom(
            $email->value('from_address', config('mail.from.address')),
            $email->value('from_name', config('mail.from.name')),
        );

        return $mailer;
    }

    /** Send a test e-mail; returns null on success or the error to show the admin. */
    public function testEmail(string $recipient): ?string
    {
        try {
            $this->mailer()->raw(
                'Ini adalah email uji dari ' . app_brand_name() . '. Jika Anda menerimanya, pengaturan SMTP sudah benar.',
                fn (Message $message) => $message->to($recipient)->subject('Uji koneksi email PTSP'),
            );
        } catch (Throwable $e) {
            return self::readable($e->getMessage());
        }

        return null;
    }

    /** Send a test WhatsApp message; returns null on success or the error to show the admin. */
    public function testWhatsApp(string $number): ?string
    {
        $wa = NotificationSetting::for('whatsapp');
        $url = $wa->value('api_url', config('whatsapp.api_url'));
        $token = $wa->value('api_token', config('whatsapp.api_token'));
        $sender = $wa->value('sender_id', config('whatsapp.sender_id'));
        $provider = $wa->value('provider', 'custom');

        if ($missing = WhatsAppGateways::missing($provider, $url, $token, $sender)) {
            return $missing;
        }

        try {
            $response = WhatsAppGateways::send($provider, $url, $token, $sender, $number, 'Pesan uji dari ' . app_brand_name() . '. Pengaturan WhatsApp sudah benar.');
        } catch (Throwable $e) {
            return self::readable($e->getMessage());
        }

        return match (true) {
            WhatsAppGateways::accepted($response) => null,
            $response->successful() => 'Gateway menolak pesan: ' . self::readable((string) ($response->json('reason') ?? $response->json('message') ?? 'tanpa keterangan')),
            default => 'Gateway menjawab HTTP ' . $response->status() . '.',
        };
    }

    /** Errors can echo credentials (e.g. SMTP AUTH); keep them short and generic. */
    private static function readable(string $message): string
    {
        return mb_strimwidth(preg_replace('/\s+/', ' ', $message), 0, 200, '…');
    }
}
