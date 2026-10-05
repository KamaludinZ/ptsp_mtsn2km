@extends('layouts.auth')

@section('title', 'Lupa Password - ' . app_brand_name())

@include('auth.partials.password-page-styles')

@section('content')
<div class="forgot-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-9 col-xl-8">
                <div class="forgot-card">
                    <!-- Header -->
                    <div class="forgot-header">
                        <div>
                            <div class="forgot-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h1 class="fw-bold mb-2">Lupa Password?</h1>
                            <p class="mb-0" style="opacity: 0.9;">Reset Password Akun Anda</p>

                            <!-- Features - Hidden on mobile, shown on desktop -->
                            <div class="forgot-features d-none d-md-block">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="feature-text">Link dikirim ke email</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="feature-text">Berlaku 60 menit</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="feature-text">Aman dan terenkripsi</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-redo"></i>
                                    </div>
                                    <div class="feature-text">Dapat diulang kapan saja</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <div class="form-body">
                        <h2 class="form-title">Reset Password</h2>
                        <p class="form-subtitle">Masukkan email Anda untuk mendapatkan link reset password</p>

                        <div class="info-box">
                            <p class="mb-0" style="color: #1e40af; font-size: 14px;">
                                <i class="fas fa-info-circle me-2"></i>
                                Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password Anda.
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Link reset password telah dikirim!</strong><br>
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

                        <form method="POST" action="{{ route('password.email') }}">
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

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset Password
                                </button>
                            </div>
                        </form>

                        <div class="divider">
                            <span>atau</span>
                        </div>

                        <div class="bottom-links">
                            <a href="{{ route('login') }}" class="btn btn-outline d-flex align-items-center justify-content-center">
                                <i class="fas fa-sign-in-alt me-2"></i>Kembali Login
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline d-flex align-items-center justify-content-center">
                                <i class="fas fa-home me-2"></i>Beranda
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Notice -->
                <div class="text-center mt-4 p-3" style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; backdrop-filter: blur(10px);">
                    <p class="mb-0" style="color: var(--bs-text); font-size: 13px;">
                        <i class="fas fa-envelope me-2"></i>
                        Link reset password akan dikirim ke email Anda dan berlaku selama 60 menit
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

@include('auth.partials.password-page-scripts')
