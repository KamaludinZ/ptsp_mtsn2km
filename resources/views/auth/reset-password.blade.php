@extends('layouts.auth')

@section('title', 'Atur Ulang Password - ' . app_brand_name())

@include('auth.partials.password-page-styles')

@section('content')
<div class="forgot-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-9 col-xl-8">
                <div class="forgot-card">
                    <div class="forgot-header">
                        <div>
                            <div class="forgot-icon">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                            </div>
                            <h1 class="fw-bold mb-2">Password Baru</h1>
                            <p class="mb-0" style="opacity: 0.9;">Atur ulang password akun Anda</p>

                            <div class="forgot-features d-none d-md-block">
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-ruler" aria-hidden="true"></i></div>
                                    <div class="feature-text">Minimal 8 karakter</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-font" aria-hidden="true"></i></div>
                                    <div class="feature-text">Berisi huruf dan angka</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-user-secret" aria-hidden="true"></i></div>
                                    <div class="feature-text">Jangan dipakai di situs lain</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-body">
                        <h2 class="form-title">Atur Ulang Password</h2>
                        <p class="form-subtitle">Buat password baru untuk masuk ke akun Anda</p>

                        @if ($errors->any())
                            <div class="alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                                <strong>Password belum bisa diubah.</strong>
                                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $request->email) }}"
                                       autocomplete="username" required
                                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email')
                                    <div class="text-danger mt-1" id="email-error" style="font-size: 14px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">Password baru <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" id="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           autocomplete="new-password" minlength="8" required autofocus
                                           style="padding-right: 3rem;"
                                           aria-describedby="password-help @error('password') password-error @enderror">
                                    <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-secondary"
                                            data-toggle-password="password" aria-label="Tampilkan password" aria-pressed="false">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div id="password-help" class="small text-muted mt-1">
                                    <span data-rule="length">✗ Minimal 8 karakter</span> ·
                                    <span data-rule="letters">✗ Ada huruf</span> ·
                                    <span data-rule="numbers">✗ Ada angka</span>
                                </div>
                                @error('password')
                                    <div class="text-danger mt-1" id="password-error" style="font-size: 14px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Ulangi password <span class="text-danger">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control" autocomplete="new-password" required aria-describedby="match-help">
                                <div id="match-help" class="small mt-1" aria-live="polite"></div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-check me-2" aria-hidden="true"></i>Simpan Password Baru
                                </button>
                            </div>
                        </form>

                        <div class="divider"><span>atau</span></div>

                        <div class="bottom-links">
                            <a href="{{ route('login') }}" class="btn btn-outline d-flex align-items-center justify-content-center">
                                <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Kembali Login
                            </a>
                            <a href="{{ route('password.request') }}" class="btn btn-outline d-flex align-items-center justify-content-center">
                                <i class="fas fa-redo me-2" aria-hidden="true"></i>Minta Link Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Ganti tema terang/gelap">
        <i class="fas fa-moon" id="theme-icon" aria-hidden="true"></i>
    </button>
</div>
@endsection

@include('auth.partials.password-page-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        const input = document.getElementById(button.dataset.togglePassword);
        button.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            button.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    });

    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const rules = {
        length: v => v.length >= 8,
        letters: v => /[A-Za-z]/.test(v),
        numbers: v => /[0-9]/.test(v),
    };
    const check = function () {
        Object.entries(rules).forEach(([name, test]) => {
            const el = document.querySelector('[data-rule="' + name + '"]');
            const ok = test(password.value);
            el.textContent = (ok ? '✓ ' : '✗ ') + el.textContent.slice(2);
            el.className = ok ? 'text-success' : '';
        });
        const help = document.getElementById('match-help');
        if (!confirmation.value) { help.textContent = ''; return; }
        const same = confirmation.value === password.value;
        help.textContent = same ? '✓ Password sama' : '✗ Password belum sama';
        help.className = 'small mt-1 ' + (same ? 'text-success' : 'text-danger');
    };
    password.addEventListener('input', check);
    confirmation.addEventListener('input', check);
});
</script>
@endpush
