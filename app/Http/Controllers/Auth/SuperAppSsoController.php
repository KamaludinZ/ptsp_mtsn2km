<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Penerima tiket login Super App Madrasah (SSO tiket).
 *
 * Pengguna (siswa, guru, pegawai) klik kartu PTSP di dashboard Super App, lalu Super App mengarahkan ke
 * GET /sso/masuk?tiket=... (tiket berlaku 60 detik, sekali pakai). Tiket ditukar ke Super App dari server
 * (kunci rahasia di header), akun PTSP dicocokkan lewat e-mail, lalu sesi login biasa dibuat.
 * Tidak ada password yang dikirim atau disimpan.
 */
class SuperAppSsoController extends Controller
{
    /** Sudah diatur di .env? Tombol di halaman login hanya tampil bila ya. */
    public static function enabled(): bool
    {
        return filled(config('superapp_sso.url')) && filled(config('superapp_sso.kunci')) && filled(self::portalUrl());
    }

    /** Dashboard Super App, tujuan tombol "Masuk lewat Super App". */
    public static function portalUrl(): ?string
    {
        $portal = config('superapp_sso.portal');
        if (filled($portal)) {
            return $portal;
        }

        $api = (string) config('superapp_sso.url');

        return $api === '' ? null : preg_replace('#/api/sso/?$#', '', rtrim($api, '/')).'/';
    }

    public function masuk(Request $request): RedirectResponse
    {
        $tiket = (string) $request->query('tiket', '');
        if ($tiket === '' || strlen($tiket) > 200) {
            return $this->gagal('Tiket tidak ditemukan. Buka PTSP dari dashboard Super App.');
        }
        if (! config('superapp_sso.url') || ! config('superapp_sso.kunci')) {
            Log::error('SSO Super App belum diatur: isi SUPERAPP_SSO_URL dan SUPERAPP_SSO_KUNCI di .env');

            return $this->gagal('Login lewat Super App belum diatur di aplikasi ini.');
        }

        try {
            $respons = Http::withToken(config('superapp_sso.kunci'))
                ->acceptJson()
                ->timeout((int) config('superapp_sso.timeout', 10))
                ->post(rtrim(config('superapp_sso.url'), '/').'/tukar', ['tiket' => $tiket]);
        } catch (ConnectionException $e) {
            Log::warning('SSO Super App tidak dapat dihubungi', ['pesan' => $e->getMessage()]);

            return $this->gagal('Super App tidak dapat dihubungi. Coba lagi beberapa saat.');
        }

        if (! $respons->successful()) {
            Log::warning('SSO Super App menolak tiket', [
                'status' => $respons->status(),
                'kode' => $respons->json('detail.kode'),
            ]);

            return $this->gagal($this->pesanGalat($respons->json('detail.kode')));
        }

        $pengguna = (array) $respons->json('pengguna', []);
        $email = mb_strtolower(trim((string) ($pengguna['email'] ?? '')));
        $catatan = $email !== '' ? $email : 'superapp:'.($pengguna['username'] ?? '?');

        // Pemeriksaan ulang di sisi PTSP (hak akses utama diatur admin Super App).
        if (! in_array($pengguna['jenis'] ?? null, (array) config('superapp_sso.jenis_diizinkan', []), true)) {
            LoginAttempt::log($catatan, false, 'SSO Super App: jenis tidak diizinkan');

            return $this->gagal('Akun Anda tidak berhak membuka PTSP lewat Super App.');
        }

        $user = $this->cariPengguna($pengguna);
        if (! $user) {
            LoginAttempt::log($catatan, false, 'SSO Super App: akun tidak ditemukan');

            return $this->gagal('Akun Anda belum terdaftar di PTSP. Pastikan e-mail di Super App sama dengan e-mail akun PTSP, atau hubungi petugas PTSP.');
        }
        if ($user->is_active === false) {
            LoginAttempt::log($user->email, false, 'SSO Super App: akun nonaktif');

            return $this->gagal('Akun Anda dinonaktifkan. Silakan hubungi petugas PTSP.');
        }

        // Masih masuk dengan akun lain (atau sedang "ganti akun")? Tutup dulu sesi itu.
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
        }

        Auth::login($user);
        $request->session()->regenerate();
        LoginAttempt::log($user->email, true);

        // Sama seperti login biasa: staf berperan ganda langsung memakai peran terakhir/pertamanya.
        \App\Support\ActiveRoles::startSession($user, $request->session());

        return redirect()->intended(get_dashboard_route_for_user($user));
    }

    /** Cari akun PTSP sesuai urutan config('superapp_sso.cocokkan'). */
    protected function cariPengguna(array $pengguna): ?User
    {
        foreach ((array) config('superapp_sso.cocokkan', []) as $kunci => $kolom) {
            $nilai = trim((string) ($pengguna[$kunci] ?? ''));
            if ($nilai === '') {
                continue;
            }
            if ($kolom === 'email') {
                $nilai = mb_strtolower($nilai); // e-mail disimpan huruf kecil
            }
            if ($user = User::where($kolom, $nilai)->first()) {
                return $user;
            }
        }

        return null;
    }

    /** Pesan yang mudah dipahami untuk kode galat dari Super App. */
    protected function pesanGalat(?string $kode): string
    {
        return match ($kode) {
            'tiket_kedaluwarsa' => 'Tiket login sudah kedaluwarsa. Buka lagi PTSP dari dashboard Super App.',
            'tiket_sudah_dipakai', 'tiket_tidak_valid' => 'Tiket login tidak berlaku. Buka lagi PTSP dari dashboard Super App.',
            'tidak_berhak' => 'Akun Anda tidak berhak membuka PTSP lewat Super App.',
            'pengguna_nonaktif' => 'Akun Super App Anda dinonaktifkan.',
            'kunci_tidak_valid', 'aplikasi_nonaktif' => 'Login lewat Super App sedang tidak tersedia. Silakan masuk dengan e-mail dan password.',
            default => 'Login lewat Super App gagal. Silakan coba lagi.',
        };
    }

    protected function gagal(string $pesan): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['sso' => $pesan]);
    }
}
