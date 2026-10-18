<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Support\NotificationTemplates;
use App\Support\WhatsAppGateways;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends through the gateways configured on the Integrasi Notifikasi page, per
 * line (jalur). The pelayanan line falls back to .env for anything not saved
 * yet; the pengaduan line only ever uses what was saved for it.
 */
class NotificationGateway
{
    /** SMTP mailer config of a line's email, "from" included. */
    public static function smtpConfig(string $line = 'pelayanan'): array
    {
        $email = NotificationSetting::for('email', $line);
        $env = fn (string $key) => $line === 'pelayanan' ? config($key) : null;
        $encryption = $email->value('encryption', $env('mail.mailers.smtp.encryption'));

        return [
            'transport' => 'smtp',
            'host' => $email->value('host', $env('mail.mailers.smtp.host')),
            'port' => (int) $email->value('port', $env('mail.mailers.smtp.port')),
            'encryption' => $encryption === 'none' ? null : $encryption,
            'username' => $email->value('username', $env('mail.mailers.smtp.username')),
            'password' => $email->value('password', $env('mail.mailers.smtp.password')),
            'timeout' => 15,
            'from' => [
                'address' => $email->value('from_address', $env('mail.from.address')),
                'name' => $email->value('from_name', $env('mail.from.name')),
            ],
        ];
    }

    public function mailer(string $line = 'pelayanan'): Mailer
    {
        $config = self::smtpConfig($line);

        $mailer = Mail::build($config);
        $mailer->alwaysFrom($config['from']['address'], $config['from']['name']);

        return $mailer;
    }

    /** Send a test e-mail on $line; returns null on success or the error to show the admin. */
    public function testEmail(string $recipient, string $line = 'pelayanan', ?string $subject = null, ?string $body = null): ?string
    {
        $config = self::smtpConfig($line);
        if (blank($config['host']) || blank($config['from']['address'])) {
            return 'Host SMTP dan alamat pengirim email belum lengkap.';
        }

        try {
            $this->mailer($line)->raw(
                $body ?? 'Ini adalah email uji dari ' . app_brand_name() . '. Jika Anda menerimanya, pengaturan SMTP sudah benar.',
                fn (Message $message) => $message->to($recipient)->subject($subject ?? 'Uji koneksi email PTSP'),
            );
        } catch (Throwable $e) {
            return self::readable($e->getMessage());
        }

        return null;
    }

    /** Send a test WhatsApp message on $line; returns null on success or the error to show the admin. */
    public function testWhatsApp(string $number, string $line = 'pelayanan', ?string $message = null): ?string
    {
        $wa = NotificationSetting::for('whatsapp', $line);
        $env = fn (string $key) => $line === 'pelayanan' ? config($key) : null;
        $url = $wa->value('api_url', $env('whatsapp.api_url'));
        $token = $wa->value('api_token', $env('whatsapp.api_token'));
        $sender = $wa->value('sender_id', $env('whatsapp.sender_id'));
        $provider = $wa->value('provider', 'custom');

        if ($missing = WhatsAppGateways::missing($provider, $url, $token, $sender)) {
            return $missing;
        }

        try {
            $response = WhatsAppGateways::send($provider, $url, $token, $sender, $number, $message ?? 'Pesan uji dari ' . app_brand_name() . '. Pengaturan WhatsApp sudah benar.');
        } catch (Throwable $e) {
            return self::readable($e->getMessage());
        }

        return match (true) {
            WhatsAppGateways::accepted($response) => null,
            $response->successful() => 'Gateway menolak pesan: ' . self::readable((string) ($response->json('reason') ?? $response->json('message') ?? 'tanpa keterangan')),
            default => 'Gateway menjawab HTTP ' . $response->status() . '.',
        };
    }

    /**
     * Uji kirim satu template: rendered with $values (sample data) and sent through
     * its channel on its event's line to $recipient, marked as a test. Works even
     * while the channel or the template is switched off, so it can be checked
     * before going live.
     */
    public function testTemplate(NotificationTemplate $template, string $recipient, array $values): ?string
    {
        $body = '[UJI] ' . NotificationTemplates::render($template->body, $values);
        $line = NotificationTemplates::line($template->key);

        return $template->channel === 'email'
            ? $this->testEmail($recipient, $line, '[UJI] ' . NotificationTemplates::render((string) ($template->subject ?: NotificationTemplates::EVENTS[$template->key] ?? $template->key), $values), $body)
            : $this->testWhatsApp($recipient, $line, $body);
    }

    /** Errors can echo credentials (e.g. SMTP AUTH); keep them short and generic. */
    private static function readable(string $message): string
    {
        return mb_strimwidth(preg_replace('/\s+/', ' ', $message), 0, 200, '…');
    }
}
