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
        'balesotomatis' => 'BalesOtomatis.id',
    ];

    public const DESCRIPTIONS = [
        'custom' => 'Kirim POST JSON {sender, number, message} dengan header Authorization: Bearer <token>.',
        'fonnte' => 'Token perangkat dari dasbor Fonnte; nomor pengirim mengikuti perangkat yang terhubung.',
        'wablas' => 'Token dari dasbor Wablas; ganti domain server pada URL sesuai akun Anda.',
        'balesotomatis' => 'API key dari Member Area (Integration > Access Token); isi ID pengirim dengan Number ID perangkat yang terhubung.',
    ];

    /** Endpoint suggested when a provider is picked (the admin may still change it). */
    public const DEFAULT_URLS = [
        'custom' => null,
        'fonnte' => 'https://api.fonnte.com/send',
        'wablas' => 'https://solo.wablas.com/api/send-message',
        'balesotomatis' => 'https://api.balesotomatis.id/public/v1/send_personal_message',
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
            'balesotomatis' => $http->asJson()->post($url, [
                'api_key' => $token,
                'number_id' => $sender,
                'enable_typing' => '0',
                'method_send' => 'sync',
                'phone_no' => self::localNumber($number),
                'country_code' => '62',
                'message' => $message,
            ]),
            default => $http->withToken($token)->post($url, ['sender' => $sender, 'number' => $number, 'message' => $message]),
        };
    }

    /**
     * Fonnte and Wablas answer HTTP 200 with {"status": false} when they refuse a
     * message; BalesOtomatis.id puts its own error code (403, 404, …) in "code".
     */
    public static function accepted(Response $response): bool
    {
        $code = $response->json('code');

        return $response->successful()
            && $response->json('status') !== false
            && ! (is_numeric($code) && (int) $code >= 400);
    }

    /** BalesOtomatis.id takes the number without its country code (62 is sent apart). */
    public static function localNumber(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        return preg_replace('/^(62|0)/', '', $digits);
    }

    /** What is missing before messages can be sent (null when complete). */
    public static function missing(?string $provider, ?string $url, ?string $token, ?string $sender): ?string
    {
        return ! $url || ! $token || (self::needsSender($provider) && ! $sender)
            ? (self::needsSender($provider) ? 'URL API, token, dan ID pengirim WhatsApp belum lengkap.' : 'URL API dan token WhatsApp belum lengkap.')
            : null;
    }
}
