<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Masuk dan keluar lewat API (Sanctum token). Same rules as the website:
 * locked after 5 failed attempts, deactivated accounts refused, applicants
 * need a verified e-mail address.
 */
class AuthController extends Controller
{
    public const MAX_ATTEMPTS = 5;

    public const TOKEN_DAYS = 30;

    /**
     * Kemampuan a token may be limited to at login (kemampuan[]); without
     * them the token can do whatever its account may ("*"). The account's
     * role and active role still decide on every request.
     */
    public const TOKEN_ABILITIES = [
        'penomoran-sk:baca' => 'Register Penomoran SK: lihat, cari, unduh laporan dan scan',
        'penomoran-sk:tulis' => 'Register Penomoran SK: minta nomor, ubah, hapus, unggah dan hapus scan',
        'jenis-sk:kelola' => 'Master Jenis SK: tambah, ubah, dan hapus jenis SK',
    ];

    /** POST /api/auth/masuk {email, password, perangkat?} */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'perangkat' => ['nullable', 'string', 'max:100'],
            'kemampuan' => ['nullable', 'array', 'max:' . count(self::TOKEN_ABILITIES)],
            'kemampuan.*' => ['distinct', Rule::in(array_keys(self::TOKEN_ABILITIES))],
        ], ['kemampuan.*.in' => 'Kemampuan token yang tersedia: ' . implode(', ', array_keys(self::TOKEN_ABILITIES)) . '.']);
        $email = mb_strtolower(trim($data['email']));
        $key = 'api-login|' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            LoginAttempt::log($email, false, 'Rate limited (API)');

            return response()->json([
                'message' => 'Terlalu banyak percobaan masuk. Coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
                'coba_lagi_dalam_detik' => $seconds,
            ], 429)->header('Retry-After', (string) $seconds);
        }

        $user = User::where('email', $email)->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key);
            LoginAttempt::log($email, false, 'Invalid credentials (API)');

            return response()->json(['message' => 'Email atau password salah.', 'errors' => ['email' => ['Email atau password salah.']]], 422);
        }

        if ($user->is_active === false) {
            LoginAttempt::log($email, false, 'Inactive account (API)');

            return response()->json(['message' => 'Akun Anda dinonaktifkan. Silakan hubungi petugas PTSP.'], 403);
        }

        if (! $user->isStaff() && ! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Verifikasi email Anda terlebih dahulu melalui tautan yang kami kirim.'], 403);
        }

        RateLimiter::clear($key);
        LoginAttempt::log($email, true);
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        activity('access')->causedBy($user)->withProperties(['ip' => $request->ip(), 'guard' => 'api'])->log('Masuk (API)');

        $device = $data['perangkat'] ?? Str::limit((string) $request->userAgent(), 100, '') ?: 'API';
        $abilities = array_values($data['kemampuan'] ?? []) ?: ['*'];
        $token = $user->createToken($device, $abilities, now()->addDays(self::TOKEN_DAYS));

        return response()->json([
            'token' => $token->plainTextToken,
            'jenis_token' => 'Bearer',
            'berlaku_sampai' => $token->accessToken->expires_at?->toIso8601String(),
            'kemampuan' => $abilities,
            'pengguna' => self::profile($user),
        ]);
    }

    /** GET /api/auth/saya: the account, and what the token in use may do. */
    public function me(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        return response()->json([
            ...self::profile($request->user()),
            'kemampuan_token' => $token instanceof \Laravel\Sanctum\PersonalAccessToken ? array_values($token->abilities ?? []) : ['*'],
        ]);
    }

    /** GET /api/auth/pengingat-kata-sandi: apakah pengguna perlu diingatkan mengganti kata sandi. */
    public function passwordRotation(Request $request): JsonResponse
    {
        return response()->json(\App\Support\PasswordRotation::status($request->user()));
    }

    /** POST /api/auth/pengingat-kata-sandi/abaikan: pengingat akun hasil import hanya tampil sekali. */
    public function dismissPasswordRotation(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->must_change_password) {
            $user->forceFill(['must_change_password' => false])->saveQuietly();
        }

        return response()->json(\App\Support\PasswordRotation::status($user));
    }

    /** POST /api/auth/keluar: revokes the token used for this request. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        activity('access')->causedBy($request->user())->withProperties(['ip' => $request->ip(), 'guard' => 'api'])->log('Keluar (API)');

        return response()->json(['message' => 'Anda telah keluar.']);
    }

    /** POST /api/auth/keluar-semua: revokes every token of the account (all devices). */
    public function logoutEverywhere(Request $request): JsonResponse
    {
        $count = $request->user()->tokens()->delete();
        activity('access')->causedBy($request->user())->withProperties(['ip' => $request->ip(), 'guard' => 'api', 'tokens' => $count])->log('Keluar dari semua perangkat (API)');

        return response()->json(['message' => 'Anda telah keluar dari semua perangkat.', 'token_dicabut' => $count]);
    }

    /** PUT /api/auth/kata-sandi {kata_sandi_lama, kata_sandi_baru, kata_sandi_baru_confirmation} */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'kata_sandi_lama' => ['required', 'string', 'current_password:sanctum'],
            'kata_sandi_baru' => ['required', 'string', 'confirmed', 'different:kata_sandi_lama', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ], [
            'kata_sandi_lama.current_password' => 'Kata sandi saat ini tidak cocok.',
            'kata_sandi_baru.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        $user->forceFill(['password' => Hash::make($data['kata_sandi_baru'])])->save();
        // Other devices must sign in again with the new password.
        $revoked = $user->tokens()->whereKeyNot($user->currentAccessToken()?->getKey())->delete();
        activity('audit')->causedBy($user)->performedOn($user)->log('Mengganti kata sandi (API)');

        return response()->json(['message' => 'Kata sandi diganti.', 'perangkat_lain_dikeluarkan' => $revoked]);
    }

    /** POST /api/auth/lupa-kata-sandi {email}: the same answer whether or not the address exists. */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $request->validate(['email' => ['required', 'email']]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        if ($status === \Illuminate\Support\Facades\Password::RESET_THROTTLED) {
            return response()->json(['message' => 'Tunggu sebentar sebelum meminta tautan lagi.'], 429);
        }

        return response()->json(['message' => 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.']);
    }

    /** POST /api/auth/reset-kata-sandi {token, email, kata_sandi_baru, kata_sandi_baru_confirmation} */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'kata_sandi_baru' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            ['email' => $data['email'], 'token' => $data['token'], 'password' => $data['kata_sandi_baru'], 'password_confirmation' => $data['kata_sandi_baru']],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                $user->tokens()->delete(); // every device signs in again
                activity('audit')->causedBy($user)->performedOn($user)->log('Mengatur ulang kata sandi (API)');
            },
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? response()->json(['message' => 'Kata sandi diatur ulang. Silakan masuk dengan kata sandi baru.'])
            : response()->json(['message' => 'Tautan tidak berlaku atau sudah kedaluwarsa. Minta tautan baru.', 'errors' => ['token' => [__($status)]]], 422);
    }

    public static function profile(User $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->name,
            'email' => $user->email,
            'whatsapp' => $user->whatsapp_number,
            'kategori' => $user->user_type,
            'peran' => $user->getRoleNames()->values(),
            'peran_label' => RoleAccess::userRoleLabel($user),
            'petugas' => $user->isStaff(),
            // Aturan pemohon resmi (memegang peran pemohon); guru yang juga waka: petugas sekaligus pemohon.
            'pemohon' => $user->isApplicant(),
            'area' => collect(['frontdesk.access' => 'loket', 'backoffice.access' => 'back_office', 'supervision.access' => 'pengawasan'])
                ->filter(fn ($area, $permission) => $user->can($permission))->values(),
            'email_terverifikasi' => $user->hasVerifiedEmail(),
            'terakhir_masuk' => $user->last_login_at?->toIso8601String(),
        ];
    }
}
