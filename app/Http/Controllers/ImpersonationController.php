<?php

namespace App\Http\Controllers;

use App\Exceptions\ImpersonationException;
use App\Models\User;
use App\Support\Impersonation;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Ganti akun sementara: administrator masuk sebagai pengguna lain (dan kembali). */
class ImpersonationController extends Controller
{
    /** POST /ganti-akun/{user} {alasan}: mulai memakai akun $user, lalu ke dasbornya. */
    public function start(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'min:10', 'max:500']], [
            'alasan.required' => 'Tuliskan alasan ganti akun.',
            'alasan.min' => 'Tuliskan alasan ganti akun (minimal 10 karakter).',
        ]);

        try {
            Impersonation::start($request->user(), $user, $data['alasan']);
        } catch (ImpersonationException $e) {
            Notification::make()->danger()->title('Tidak dapat ganti akun')->body($e->getMessage())->send();

            return back();
        }

        Notification::make()->warning()
            ->title('Anda memakai akun ' . $user->name)
            ->body('Sesi ini dicatat. Pilih "Kembali ke akun admin" bila sudah selesai.')
            ->send();

        return redirect(get_dashboard_route_for_user($user));
    }

    /** POST /ganti-akun/selesai: akhiri sesi, kembali ke akun administrator dan dasbornya. */
    public function end(Request $request): RedirectResponse|JsonResponse
    {
        $ended = Impersonation::end('selesai');

        if ($request->expectsJson()) {
            return response()->json([
                'diakhiri' => (bool) $ended,
                'akun_dipakai' => $ended ? $ended['target']?->name : null,
                'akun_aktif' => auth()->user()?->name,
            ], $ended ? 200 : 409);
        }

        Notification::make()->info()
            ->title($ended ? 'Kembali ke akun admin' : 'Tidak ada sesi ganti akun')
            ->body($ended ? 'Sesi memakai akun ' . ($ended['target']?->name ?? 'pengguna') . ' diakhiri dan dicatat.' : null)
            ->send();

        return $ended && auth()->check() ? redirect(get_dashboard_route_for_user(auth()->user())) : back();
    }
}
