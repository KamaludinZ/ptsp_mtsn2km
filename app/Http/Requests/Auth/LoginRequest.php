<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginAttempt;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    /** E-mail addresses are stored in lower case. */
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');

        // Validasi captcha - membandingkan input captcha dengan session
        $captchaInput = $this->input('captcha');
        $storedCaptcha = session('captcha_value'); // Ambil dari session

        if (empty($captchaInput) || empty($storedCaptcha) || strtoupper($captchaInput) !== strtoupper($storedCaptcha)) {
            // Log failed attempt - invalid captcha (counts toward the lockout too)
            RateLimiter::hit($this->throttleKey());
            LoginAttempt::log($email, false, 'Invalid CAPTCHA');

            throw ValidationException::withMessages([
                'captcha' => 'Kode verifikasi tidak valid. Silakan coba lagi.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Log failed attempt - invalid credentials
            LoginAttempt::log($email, false, 'Invalid credentials');

            // Get number of recent failed attempts
            $failedAttempts = LoginAttempt::getRecentFailedAttempts($email, 60);
            $remainingAttempts = max(0, 5 - $failedAttempts);

            $message = trans('auth.failed');
            if ($remainingAttempts <= 2 && $remainingAttempts > 0) {
                $message .= " Tersisa {$remainingAttempts} percobaan sebelum akun diblokir sementara.";
            }

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        // A deactivated account may not sign in anywhere.
        if (Auth::user()?->is_active === false) {
            Auth::logout();
            LoginAttempt::log($email, false, 'Inactive account');

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi petugas PTSP.',
            ]);
        }

        // Log successful login
        LoginAttempt::log($email, true);

        RateLimiter::clear($this->throttleKey());

        // Hapus captcha dari session setelah berhasil login
        session()->forget('captcha_value');
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Log rate limit attempt
        LoginAttempt::log($this->input('email'), false, 'Rate limited');

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);
        session()->flash('login_locked_until', now()->addSeconds($seconds)->timestamp); // countdown on the form

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Akun Anda diblokir sementara selama {$minutes} menit. Silakan coba lagi nanti.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
