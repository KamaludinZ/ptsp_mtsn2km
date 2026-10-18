<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Services\NotificationGateway;
use App\Support\WhatsAppGateways;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pengaturan integrasi Email & WhatsApp (admin), per line: ?jalur=pengaduan
 * (pengaduan, saran & WBS) or the default jalur pelayanan. Secrets are
 * write-only: responses only say whether one is stored.
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
            'provider' => ['nullable', 'in:custom,fonnte,wablas,balesotomatis'],
            'api_url' => ['required_if:aktif,true', 'nullable', 'url'],
            'api_token' => ['nullable', 'string', 'max:500'],
            // Required for the custom gateway only; see update().
            'sender_id' => ['nullable', 'string', 'max:50'],
        ],
    ];

    /** GET /api/integrasi/{channel} */
    public function show(Request $request, string $channel): JsonResponse
    {
        $line = $this->authorizeAdmin($request, $channel);

        return response()->json($this->present(NotificationSetting::for($channel, $line)));
    }

    /** PUT /api/integrasi/{channel}: blank secrets keep the stored ones. */
    public function update(Request $request, string $channel): JsonResponse
    {
        $line = $this->authorizeAdmin($request, $channel);

        $rules = self::RULES[$channel];
        if ($channel === 'whatsapp') {
            $provider = $request->input('provider', NotificationSetting::for('whatsapp', $line)->value('provider', 'custom'));
            $rules['sender_id'][] = Rule::requiredIf(fn () => $request->boolean('aktif') && WhatsAppGateways::needsSender($provider));
        }

        $data = $request->validate($rules);
        $setting = NotificationSetting::store($channel, (bool) $data['aktif'], collect($data)->except('aktif')->filter(fn ($v) => $v !== null)->all(), $request->user()->id, $line);

        activity('audit')->causedBy($request->user())->withProperties(['channel' => $channel, 'line' => $line, 'enabled' => $setting->is_enabled])->log('Mengubah pengaturan integrasi ' . $channel . self::lineSuffix($line));

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
        $line = $this->authorizeAdmin($request, $channel);

        $enabled = (bool) $request->validate(['aktif' => ['required', 'boolean']])['aktif'];
        $setting = NotificationSetting::for($channel, $line);

        $missing = collect(self::REQUIRED[$channel])
            ->reject(fn (string $key) => $key === 'sender_id' && ! WhatsAppGateways::needsSender($setting->value('provider')))
            ->reject(fn (string $key) => filled($setting->value($key)))->values();
        if ($enabled && $missing->isNotEmpty()) {
            return response()->json(['message' => 'Lengkapi pengaturan gateway terlebih dahulu: ' . $missing->join(', ') . '.'], 422);
        }

        $setting->fill(['is_enabled' => $enabled, 'updated_by' => $request->user()->id])->save();
        activity('audit')->causedBy($request->user())->withProperties(['channel' => $channel, 'line' => $line, 'enabled' => $enabled])->log(($enabled ? 'Mengaktifkan' : 'Menonaktifkan') . ' notifikasi ' . $channel . self::lineSuffix($line));

        return response()->json($this->present($setting));
    }

    /** POST /api/integrasi/{channel}/uji: send a test with the saved settings. */
    public function test(Request $request, string $channel, NotificationGateway $gateway): JsonResponse
    {
        $line = $this->authorizeAdmin($request, $channel);

        $error = $channel === 'email'
            ? $gateway->testEmail($request->validate(['penerima' => ['required', 'email']])['penerima'], $line)
            : $gateway->testWhatsApp($request->validate(['nomor' => ['required', 'regex:/^[0-9+]{8,20}$/']])['nomor'], $line);

        return $error === null
            ? response()->json(['berhasil' => true, 'pesan' => 'Pesan uji terkirim.'])
            : response()->json(['berhasil' => false, 'pesan' => $error], 422);
    }

    /** @return string the line asked for (?jalur=), jalur pelayanan by default */
    private function authorizeAdmin(Request $request, string $channel): string
    {
        abort_unless(array_key_exists($channel, NotificationSetting::CHANNELS), 404);
        abort_unless($request->user()->hasRole('admin'), 403);

        return $request->validate(['jalur' => ['nullable', Rule::in(array_keys(NotificationSetting::LINES))]])['jalur'] ?? 'pelayanan';
    }

    private static function lineSuffix(string $line): string
    {
        return $line === 'pelayanan' ? '' : " (jalur {$line})";
    }

    private function present(NotificationSetting $setting): array
    {
        return [
            'kanal' => $setting->channel,
            'jalur' => $setting->line,
            'aktif' => (bool) $setting->is_enabled,
            'konfigurasi' => $setting->publicConfig(),
            'diperbarui' => $setting->updated_at?->toIso8601String(),
        ];
    }
}
