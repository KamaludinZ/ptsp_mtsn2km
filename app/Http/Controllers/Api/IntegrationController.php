<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Services\NotificationGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pengaturan integrasi Email & WhatsApp (admin). Secrets are write-only:
 * responses only say whether one is stored.
 */
class IntegrationController extends Controller
{
    private const RULES = [
        'email' => [
            'aktif' => ['required', 'boolean'],
            'host' => ['required_if:aktif,true', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:aktif,true', 'nullable', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'in:tls,ssl,none'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required_if:aktif,true', 'nullable', 'email'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ],
        'whatsapp' => [
            'aktif' => ['required', 'boolean'],
            'api_url' => ['required_if:aktif,true', 'nullable', 'url'],
            'api_token' => ['nullable', 'string', 'max:500'],
            'sender_id' => ['required_if:aktif,true', 'nullable', 'string', 'max:50'],
        ],
    ];

    /** GET /api/integrasi/{channel} */
    public function show(Request $request, string $channel): JsonResponse
    {
        $this->authorizeAdmin($request, $channel);

        return response()->json($this->present(NotificationSetting::for($channel)));
    }

    /** PUT /api/integrasi/{channel}: blank secrets keep the stored ones. */
    public function update(Request $request, string $channel): JsonResponse
    {
        $this->authorizeAdmin($request, $channel);

        $data = $request->validate(self::RULES[$channel]);
        $setting = NotificationSetting::store($channel, (bool) $data['aktif'], collect($data)->except('aktif')->filter(fn ($v) => $v !== null)->all(), $request->user()->id);

        activity('audit')->causedBy($request->user())->withProperties(['channel' => $channel, 'enabled' => $setting->is_enabled])->log('Mengubah pengaturan integrasi ' . $channel);

        return response()->json($this->present($setting));
    }

    /** Settings a channel needs before it may be switched on. */
    private const REQUIRED = [
        'email' => ['host', 'port', 'from_address'],
        'whatsapp' => ['api_url', 'api_token', 'sender_id'],
    ];

    /** PATCH /api/integrasi/{channel}/aktif {aktif: bool} */
    public function toggle(Request $request, string $channel): JsonResponse
    {
        $this->authorizeAdmin($request, $channel);

        $enabled = (bool) $request->validate(['aktif' => ['required', 'boolean']])['aktif'];
        $setting = NotificationSetting::for($channel);

        $missing = collect(self::REQUIRED[$channel])->reject(fn (string $key) => filled($setting->value($key)))->values();
        if ($enabled && $missing->isNotEmpty()) {
            return response()->json(['message' => 'Lengkapi pengaturan gateway terlebih dahulu: ' . $missing->join(', ') . '.'], 422);
        }

        $setting->fill(['is_enabled' => $enabled, 'updated_by' => $request->user()->id])->save();
        activity('audit')->causedBy($request->user())->withProperties(['channel' => $channel, 'enabled' => $enabled])->log(($enabled ? 'Mengaktifkan' : 'Menonaktifkan') . ' notifikasi ' . $channel);

        return response()->json($this->present($setting));
    }

    /** POST /api/integrasi/{channel}/uji: send a test with the saved settings. */
    public function test(Request $request, string $channel, NotificationGateway $gateway): JsonResponse
    {
        $this->authorizeAdmin($request, $channel);

        $error = $channel === 'email'
            ? $gateway->testEmail($request->validate(['penerima' => ['required', 'email']])['penerima'])
            : $gateway->testWhatsApp($request->validate(['nomor' => ['required', 'regex:/^[0-9+]{8,20}$/']])['nomor']);

        return $error === null
            ? response()->json(['berhasil' => true, 'pesan' => 'Pesan uji terkirim.'])
            : response()->json(['berhasil' => false, 'pesan' => $error], 422);
    }

    private function authorizeAdmin(Request $request, string $channel): void
    {
        abort_unless(array_key_exists($channel, NotificationSetting::CHANNELS), 404);
        abort_unless($request->user()->hasRole('admin'), 403);
    }

    private function present(NotificationSetting $setting): array
    {
        return [
            'kanal' => $setting->channel,
            'aktif' => (bool) $setting->is_enabled,
            'konfigurasi' => $setting->publicConfig(),
            'diperbarui' => $setting->updated_at?->toIso8601String(),
        ];
    }
}
