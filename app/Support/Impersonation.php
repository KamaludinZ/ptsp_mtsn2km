<?php

namespace App\Support;

use App\Exceptions\ImpersonationException;
use App\Models\ImpersonationSession;
use App\Models\User;
use BadMethodCallException;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Sesi ganti akun sementara (impersonation): administrator yang sedang
 * memakai akun pengguna lain. Sesi menyimpan
 * ['admin_id', 'log_id', 'started_at' (ISO-8601), 'reason'] dan setiap sesi
 * dicatat di impersonation_sessions; penanda di layar membacanya dari sini.
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonation';

    /**
     * @return ?array{admin: ?User, admin_name: string, target: ?User, log_id: ?int, started_at: ?Carbon, reason: ?string}
     */
    public static function current(): ?array
    {
        $data = session(self::SESSION_KEY);
        if (! is_array($data) || empty($data['admin_id'])) {
            return null;
        }

        $admin = User::find($data['admin_id']);

        return [
            'admin' => $admin,
            'admin_name' => $admin?->name ?? 'Administrator',
            // The account in use is the signed-in one.
            'target' => isset($data['target_id']) ? User::find($data['target_id']) : auth()->user(),
            'log_id' => $data['log_id'] ?? null,
            'started_at' => isset($data['started_at']) ? Carbon::parse($data['started_at']) : null,
            'reason' => $data['reason'] ?? null,
        ];
    }

    public static function active(): bool
    {
        return self::current() !== null;
    }

    /** Why $admin may not use $target's account now, or null when they may. */
    public static function refusal(User $admin, User $target): ?string
    {
        return match (true) {
            self::active() => 'Akhiri dulu sesi ganti akun yang sedang berjalan.',
            ! $admin->is(auth()->user()) || ! ActiveRoles::hasRole($admin, 'admin') => 'Ganti akun hanya dari peran aktif Administrator.',
            $target->is($admin) => 'Ini akun Anda sendiri.',
            $target->is_active === false => 'Akun nonaktif tidak dapat dimasuki.',
            default => null,
        };
    }

    /**
     * Mulai memakai akun $target atas nama $admin, dengan alasan: dicatat di
     * impersonation_sessions lalu sesi ini masuk sebagai $target.
     *
     * @throws ImpersonationException
     */
    public static function start(User $admin, User $target, string $reason): ImpersonationSession
    {
        if ($refusal = self::refusal($admin, $target)) {
            throw new ImpersonationException($refusal);
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10) {
            throw new ImpersonationException('Tuliskan alasan ganti akun (minimal 10 karakter).');
        }

        $log = ImpersonationSession::create([
            'admin_id' => $admin->getKey(),
            'admin_name' => $admin->name,
            'target_id' => $target->getKey(),
            'target_name' => $target->name,
            'target_role' => $target->getRoleNames()->first(),
            'reason' => mb_substr($reason, 0, 500),
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 200) ?: null,
            'started_at' => now(),
        ]);

        self::record($log, 'started', 'Mulai ganti akun ke ' . $target->name);
        self::signInAs($target);

        // The administrator's active role is theirs; the account in use resolves its own.
        session()->forget([ActiveRoles::SESSION_KEY, ActiveRoles::PICK_FLAG]);
        session()->put(self::SESSION_KEY, [
            'admin_id' => $admin->getKey(),
            'log_id' => $log->getKey(),
            'started_at' => $log->started_at->toIso8601String(),
            'reason' => $log->reason,
        ]);

        return $log;
    }

    /**
     * Akhiri sesi ganti akun: tutup lognya dan kembalikan sesi ini ke akun
     * administrator (atau keluarkan bila akun itu tidak lagi berwenang).
     * Mengembalikan data sesi yang diakhiri, null bila tidak ada.
     *
     * @param  string  $reason  ImpersonationSession::END_REASONS key
     */
    public static function end(string $reason = 'selesai'): ?array
    {
        $current = self::current();
        if (! $current) {
            return null;
        }

        self::closeLog($current['log_id'], $reason);
        session()->forget([self::SESSION_KEY, ActiveRoles::SESSION_KEY, ActiveRoles::PICK_FLAG]);

        $admin = $current['admin'];
        if ($admin && self::adminStillValid($admin)) {
            self::signInAs($admin);
        } else {
            Auth::guard('web')->logout();
        }

        return $current;
    }

    /**
     * Why the running session must end now, or null when it may go on:
     * its log is gone or closed, the administrator lost access, or it ran
     * past config('impersonation.max_minutes').
     */
    public static function expiryReason(): ?string
    {
        $current = self::current();
        if (! $current) {
            return null;
        }

        $log = $current['log_id'] ? ImpersonationSession::find($current['log_id']) : null;
        $limit = (int) config('impersonation.max_minutes', 120);

        return match (true) {
            ! $log || ! $log->isOpen() => 'Sesi ganti akun tidak ditemukan lagi.',
            ! $current['admin'] || ! self::adminStillValid($current['admin']) => 'Akun administrator tidak lagi berwenang.',
            $limit > 0 && $log->started_at->lt(now()->subMinutes($limit)) => "Sesi ganti akun melewati batas {$limit} menit.",
            default => null,
        };
    }

    /** Log an ended session that never came back through end() (e.g. signing out). */
    public static function closeLog(?int $logId, string $reason): void
    {
        $log = $logId ? ImpersonationSession::find($logId) : null;
        if ($log?->isOpen()) {
            $log->update(['ended_at' => now(), 'end_reason' => $reason]);
            self::record($log, 'ended', 'Mengakhiri ganti akun ' . $log->target_name . ' (' . (ImpersonationSession::END_REASONS[$reason] ?? $reason) . ')');
        }
    }

    /** Rekam jejak di activity_log (log "impersonation"): pelaku administrator, subjek akun yang dipakai. */
    private static function record(ImpersonationSession $log, string $event, string $description): void
    {
        activity('impersonation')
            ->causedBy($log->admin_id ? User::find($log->admin_id) : null)
            ->performedOn($log->target_id ? (User::find($log->target_id) ?? $log) : $log)
            ->event($event)
            ->withProperties(array_filter([
                'sesi_id' => $log->id,
                'admin' => $log->admin_name,
                'akun_dipakai' => $log->target_name,
                'peran_akun' => $log->target_role,
                'alasan' => $event === 'started' ? $log->reason : null,
                'cara_berakhir' => $log->end_reason,
                'durasi_menit' => $log->ended_at ? (int) $log->started_at->diffInMinutes($log->ended_at) : null,
                'ip' => request()->ip(),
            ], fn ($v) => $v !== null))
            ->log($description);
    }

    /** Still an active administrator account (any of its roles, not the session's active one). */
    private static function adminStillValid(User $admin): bool
    {
        return $admin->is_active !== false && ! $admin->trashed() && $admin->hasRole('admin');
    }

    /**
     * Switch the web guard to $user without the sign-in side effects
     * (Login event: "terakhir masuk" and the access log stay the target's own).
     */
    private static function signInAs(User $user): void
    {
        $guard = Auth::guard('web');
        $events = $guard->getDispatcher();
        $guard->setDispatcher(new NullDispatcher($events));

        try {
            $guard->login($user);
        } finally {
            $guard->setDispatcher($events);
        }

        // AuthenticateSession compares this hash on every request; keep it the new account's.
        $hash = $user->getAuthPassword();
        try {
            $hash = $guard->hashPasswordForCookie($hash);
        } catch (BadMethodCallException) {
        }
        session()->put('password_hash_web', $hash);
    }
}
