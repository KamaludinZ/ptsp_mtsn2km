<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Dashboard;
use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Ganti peran cepat dari header /cp: berpindah ke peran lain yang dipegang
 * lalu kembali ke halaman yang sedang dibuka, dengan toast perpindahan.
 * Hanya alamat di situs ini yang dipakai untuk kembali.
 */
class ActiveRoleSwitchController extends Controller
{
    /** POST /peran-aktif/ganti {peran, kembali?} */
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless((bool) $request->user()?->isStaff(), 403, 'Hanya akun petugas yang memiliki peran aktif.');

        $data = $request->validate([
            'peran' => ['required', 'string', 'max:125'],
            'kembali' => ['nullable', 'string', 'max:2048'],
        ], ['peran.required' => 'Pilih peran yang akan diaktifkan.']);
        $back = self::safeReturnUrl($request, $data['kembali'] ?? null);

        if ($request->expectsJson()) {
            $switch = ActiveRoles::switchTo($request->user(), $data['peran']);

            return response()->json(['sebelumnya' => $switch['from'], 'aktif' => $switch['to'], 'kembali' => $back]);
        }

        ActiveRoleToast::switchTo($request->user(), $data['peran']);

        return redirect()->to($back);
    }

    /** Halaman asal bila berada di situs ini; selain itu dasbor /cp. */
    public static function safeReturnUrl(Request $request, ?string $url): string
    {
        $fallback = Dashboard::getUrl(panel: 'admin');
        if (blank($url) || str_contains($url, '\\') || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return $fallback;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        $parts = parse_url($url);
        $sameSite = in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            && strcasecmp($parts['host'] ?? '', $request->getHost()) === 0
            && ($parts['port'] ?? null) === (parse_url($request->root(), PHP_URL_PORT) ?: null);

        return $sameSite ? $url : $fallback;
    }
}
