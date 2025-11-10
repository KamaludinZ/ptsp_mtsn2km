@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@push('styles')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 40px 16px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    }

    [data-theme="dark"] .login-container {
        background: linear-gradient(135deg, #1a2e1a 0%, #0f1e0f 50%, #071207 100%);
    }

    /* Dark Mode Toggle Button */
    .theme-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s;
        z-index: 1000;
    }

    .theme-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .login-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    [data-theme="dark"] .login-card {
        background: #111827;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }

    .login-header {
        background: var(--bs-primary);
        color: white;
        padding: 40px 32px;
        text-align: center;
    }

    .login-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
    }

    .form-body {
        padding: 40px 32px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--bs-text);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        background: #ffffff;
        color: var(--bs-text);
        transition: all 0.2s;
    }

    [data-theme="dark"] .form-control {
        background: #1f2937;
        border-color: #374151;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(20, 83, 45, 0.1);
    }

    .btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--bs-primary);
        color: #ffffff !important;
        width: 100%;
    }

    .btn-primary:hover {
        background: var(--bs-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary);
    }

    .btn-outline:hover {
        background: var(--bs-primary);
        color: #ffffff;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    [data-theme="dark"] .alert-success {
        background: #064e3b;
        border-color: #047857;
        color: #d1fae5;
    }

    .alert-danger {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .captcha-container {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .captcha-display {
        flex: 0 0 auto;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 12px 24px;
        font-family: 'Courier New', monospace;
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 8px;
        color: var(--bs-primary);
        user-select: none;
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .captcha-display {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        color: #22c55e;
    }

    .captcha-display::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 2px,
            rgba(0, 0, 0, 0.02) 2px,
            rgba(0, 0, 0, 0.02) 4px
        );
        pointer-events: none;
    }

    .captcha-refresh {
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: var(--bs-primary);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s;
    }

    .captcha-refresh:hover {
        background: var(--bs-primary-dark);
        transform: rotate(180deg);
    }

    .captcha-input {
        flex: 1;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-check-label {
        cursor: pointer;
        user-select: none;
        color: var(--bs-text);
        font-size: 14px;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 24px 0;
        color: var(--bs-secondary-text);
        font-size: 14px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--bs-border-color);
    }

    .divider span {
        padding: 0 16px;
    }

    .bottom-links {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .bottom-links a,
    .bottom-links button {
        flex: 1;
        text-align: center;
    }

    .text-link {
        color: var(--bs-primary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .text-link:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-xl-4">
                <div class="login-card">
                    <!-- Header -->
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h1 class="h3 fw-bold mb-2">Selamat Datang</h1>
                        <p class="mb-0" style="opacity: 0.9;">Masuk ke akun Anda</p>
                    </div>

                    <!-- Form Body -->
                    <div class="form-body">
                        @if (session('status'))
                            <div class="alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Terjadi kesalahan!</strong>
                                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="contoh@email.com"
                                       required
                                       autofocus>
                                @error('email')
                                    <div class="text-danger mt-1" style="font-size: 14px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Masukkan password Anda"
                                       required>
                                @error('password')
                                    <div class="text-danger mt-1" style="font-size: 14px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Kode Verifikasi <span class="text-danger">*</span>
                                </label>
                                <div class="captcha-container">
                                    <div class="captcha-display" id="captchaDisplay">{{ $captcha ?? '000000' }}</div>
                                    <button type="button"
                                            class="captcha-refresh"
                                            onclick="refreshCaptcha()"
                                            title="Refresh Captcha">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <input type="text"
                                       id="captcha"
                                       name="captcha"
                                       class="form-control mt-2 @error('captcha') is-invalid @enderror"
                                       placeholder="Masukkan 6 angka di atas"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       required>
                                @error('captcha')
                                    <div class="text-danger mt-1" style="font-size: 14px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="remember"
                                               name="remember">
                                        <label class="form-check-label" for="remember">
                                            Ingat Saya
                                        </label>
                                    </div>
                                    <a href="{{ route('password.request') }}" class="text-link">
                                        Lupa Password?
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                                </button>
                            </div>
                        </form>

                        <div class="divider">
                            <span>atau</span>
                        </div>

                        <div class="bottom-links">
                            <a href="{{ route('register') }}" class="btn btn-outline">
                                <i class="fas fa-user-plus me-2"></i>Daftar Akun
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline">
                                <i class="fas fa-home me-2"></i>Beranda
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Notice -->
                <div class="text-center mt-4 p-3" style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; backdrop-filter: blur(10px);">
                    <p class="mb-0" style="color: var(--bs-text); font-size: 13px;">
                        <i class="fas fa-shield-alt me-2"></i>
                        Login Anda dilindungi dengan captcha untuk keamanan akun
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
// Refresh captcha using AJAX
function refreshCaptcha() {
    fetch('/refresh-captcha')
        .then(response => response.json())
        .then(data => {
            document.getElementById('captchaDisplay').textContent = data.captcha;
            document.getElementById('captcha').value = '';
        })
        .catch(error => {
            console.error('Error refreshing captcha:', error);
        });
}

// Validate form before submit (client side check only)
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const captchaInput = document.getElementById('captcha').value;
    
    if (!captchaInput || captchaInput.trim() === '') {
        e.preventDefault();
        alert('Silakan masukkan kode verifikasi.');
        return false;
    }
});

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', function() {
    updateThemeIcon();
});

// Update theme icon based on current theme
function updateThemeIcon() {
    const theme = document.documentElement.getAttribute('data-theme');
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Override toggleTheme to update icon
const originalToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
    originalToggleTheme();
    updateThemeIcon();
};
</script>
@endpush
