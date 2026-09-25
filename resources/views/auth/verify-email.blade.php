@extends('layouts.auth')

@section('title', 'Verifikasi Email - ' . app_brand_name())

@push('styles')
<style>
    .verify-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 40px 16px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    }

    [data-theme="dark"] .verify-container {
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

    .verify-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        padding: 48px;
        text-align: center;
    }

    [data-theme="dark"] .verify-card {
        background: #111827;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }

    .email-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
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
    }

    .btn-primary:hover {
        background: var(--bs-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--bs-border-color);
        color: var(--bs-text);
    }

    .btn-outline:hover {
        background: var(--bs-border-color);
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    [data-theme="dark"] .alert-success {
        background: #064e3b;
        border-color: #047857;
        color: #d1fae5;
    }
</style>
@endpush

@section('content')
<div class="verify-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="verify-card">
                    <div class="email-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>

                    <h1 class="h3 fw-bold mb-3" style="color: var(--bs-text);">
                        Verifikasi Email Anda
                    </h1>

                    <p class="mb-4" style="color: var(--bs-secondary-text); line-height: 1.6;">
                        Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan ke email Anda.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Link verifikasi baru telah dikirim!</strong><br>
                            Silakan cek inbox atau folder spam email Anda.
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert-success">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mt-4 mb-4">
                        <p class="mb-2" style="color: var(--bs-secondary-text); font-size: 14px;">
                            <i class="fas fa-question-circle me-2"></i>
                            Tidak menerima email?
                        </p>
                        <form method="POST" action="{{ route('verification.send') }}" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Ulang Email Verifikasi
                            </button>
                        </form>
                    </div>

                    <div class="pt-4 mt-4" style="border-top: 1px solid var(--bs-border-color);">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-outline">
                                <i class="fas fa-sign-out-alt me-2"></i>Keluar
                            </button>
                        </form>
                        <a href="{{ url('/') }}" class="btn btn-outline ms-2">
                            <i class="fas fa-home me-2"></i>Kembali ke Beranda
                        </a>
                    </div>

                    <div class="mt-4 p-3" style="background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
                        <p class="mb-0" style="color: var(--bs-text); font-size: 13px;">
                            <i class="fas fa-shield-alt me-2"></i>
                            Jika Anda mengalami masalah, silakan hubungi administrator di
                            <a href="mailto:{{ config('mail.from.address') }}" style="color: var(--bs-primary);">
                                {{ config('mail.from.address') }}
                            </a>
                        </p>
                    </div>
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
// Update theme icon based on current theme
function updateThemeIcon() {
    const theme = document.documentElement.getAttribute('data-theme');
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', updateThemeIcon);

// Override toggleTheme to update icon
const originalToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
    originalToggleTheme();
    updateThemeIcon();
};
</script>
@endpush
