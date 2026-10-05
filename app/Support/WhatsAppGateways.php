<?php

namespace App\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * WhatsApp gateways the admin can choose on Integrasi Notifikasi. Each one
 * has its own endpoint and request shape; "custom" is any gateway that takes
 * a Bearer token and a JSON body of sender, number and message.
 */
class WhatsAppGateways
{
    public const PROVIDERS = [
        'custom' => 'Gateway lain (Bearer token, JSON)',
        'fonnte' => 'Fonnte',
        'wablas' => 'Wablas',
    ];

    public const DESCRIPTIONS = [
        'custom' => 'Kirim POST JSON {sender, number, message} dengan header Authorization: Bearer <token>.',
        'fonnte' => 'Token perangkat dari dasbor Fonnte; nomor pengirim mengikuti perangkat yang terhubung.',
        'wablas' => 'Token dari dasbor Wablas; ganti domain server pada URL sesuai akun Anda.',
    ];

    /** Endpoint suggested when a provider is picked (the admin may still change it). */
    public const DEFAULT_URLS = [
        'custom' => null,
        'fonnte' => 'https://api.fonnte.com/send',
        'wablas' => 'https://solo.wablas.com/api/send-message',
    ];

    /** Providers whose sender is the device behind the token, so no sender id is needed. */
    public const DEVICE_BOUND = ['fonnte', 'wablas'];

    public static function needsSender(?string $provider): bool
    {
        return ! in_array($provider ?: 'custom', self::DEVICE_BOUND, true);
    }

    /** Send one message in the shape the chosen gateway expects. */
    public static function send(?string $provider, string $url, string $token, ?string $sender, string $number, string $message, int $timeout = 15): Response
    {
        $http = Http::timeout($timeout);

        return match ($provider ?: 'custom') {
            'fonnte' => $http->withHeaders(['Authorization' => $token])->asForm()->post($url, ['target' => $number, 'message' => $message]),
            'wablas' => $http->withHeaders(['Authorization' => $token])->asJson()->post($url, ['phone' => $number, 'message' => $message]),
            default => $http->withToken($token)->post($url, ['sender' => $sender, 'number' => $number, 'message' => $message]),
        };
    }

    /** Fonnte and Wablas answer HTTP 200 with {"status": false} when they refuse a message. */
    public static function accepted(Response $response): bool
    {
        return $response->successful() && $response->json('status') !== false;
    }

    /** What is missing before messages can be sent (null when complete). */
    public static function missing(?string $provider, ?string $url, ?string $token, ?string $sender): ?string
    {
        return ! $url || ! $token || (self::needsSender($provider) && ! $sender)
            ? (self::needsSender($provider) ? 'URL API, token, dan ID pengirim WhatsApp belum lengkap.' : 'URL API dan token WhatsApp belum lengkap.')
            : null;
    }
}
