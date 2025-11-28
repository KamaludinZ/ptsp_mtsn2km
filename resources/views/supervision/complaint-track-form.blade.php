@extends('layouts.public')

@section('title', 'Lacak Pengaduan - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Service Component Hover Effect */
    .service-component {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.25) !important;
        backdrop-filter: blur(10px);
    }

    .service-component:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        background: rgba(255, 255, 255, 0.35) !important;
    }

    .icon-hover {
        transition: transform 0.3s ease;
    }

    .service-component:hover .icon-hover {
        transform: scale(1.2) rotate(5deg);
    }

    /* Dark mode support for stat cards */
    [data-theme="dark"] .stat-card {
        background-color: var(--bs-surface) !important;
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] .stat-card .card {
        background-color: var(--bs-surface) !important;
    }

    /* Dark mode for additional stats */
    [data-theme="dark"] .stats-additional {
        background-color: var(--bs-surface) !important;
        border-color: var(--bs-primary) !important;
    }

    /* Dark mode for performance section */
    [data-theme="dark"] .performance-section {
        background-color: var(--bs-surface) !important;
    }

    /* Komponen text always white on green gradient */
    .component-text {
        color: white !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Dark mode specific fixes */
    [data-theme="dark"] .text-muted {
        color: #d1d5db !important;
    }

    [data-theme="dark"] .lead {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] p {
        color: var(--bs-text);
    }
    
    /* Custom styles for complaint tracking page */
    .page-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
        padding: 48px 0;
        margin-bottom: 32px;
    }

    .breadcrumb {
        font-size: 14px;
        margin-bottom: 16px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
    }

    .page-subtitle {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
    }

    .form-section {
        background: #ffffff;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
    }
    
    [data-theme="dark"] .form-section {
        background: #111827;
    }
    
    .form-group {
        margin-bottom: 20px;
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
        border-radius: 6px;
        background: #ffffff;
        color: var(--bs-text);
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
        border-radius: 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    
    .btn-primary {
        background: var(--bs-primary);
        color: #ffffff !important;
    }
    
    .btn-primary:hover {
        background: var(--bs-primary-dark);
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
    
    .status-card {
        background: #ffffff;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    [data-theme="dark"] .status-card {
        background: #111827;
    }
    
    .step-icon {
        width: 48px;
        height: 48px;
        background: rgba(20, 83, 45, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .step-container {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 32px 0;
        }
        
        .page-title {
            font-size: 24px;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ url('/') }}" style="color: rgba(255,255,255,0.8);">Beranda</a>
            <span style="color: rgba(255,255,255,0.6); margin: 0 8px;">/</span>
            <span style="color: #ffffff;">Lacak Pengaduan</span>
        </nav>

        <!-- Title -->
        <h1 class="page-title">Lacak Pengaduan Anda</h1>
        <p class="page-subtitle">Masukkan nomor tiket pengaduan untuk mengetahui status terkini dari pengaduan yang telah Anda sampaikan</p>
    </div>
</div>

<!-- Main Content -->
<div class="container" style="padding: 0 16px 64px;">
    <!-- Tracking Form Section -->
    <div class="form-section">
        <h2 class="h4 fw-bold mb-4" style="color: var(--bs-text);">Formulir Pelacakan Pengaduan</h2>
        
        <form method="POST" action="{{ route('supervision.complaint.track') }}" class="space-y-6">
            @csrf
            <div class="form-group">
                <label for="complaint_number" class="form-label">
                    Nomor Tiket Pengaduan <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       id="complaint_number" 
                       name="complaint_number" 
                       required
                       class="form-control"
                       placeholder="Contoh: KPL-2025-0001">
                <p class="mt-2 text-muted" style="font-size: 0.875rem; color: var(--bs-secondary-text);">
                    Nomor tiket pengaduan biasanya berupa kombinasi huruf dan angka yang dikirim saat pengaduan berhasil dikirim
                </p>
            </div>
            
            <div class="form-group">
                <label for="reporter_email" class="form-label">
                    Email Pelapor (Opsional)
                </label>
                <input type="email" 
                       id="reporter_email" 
                       name="reporter_email" 
                       class="form-control"
                       placeholder="Email yang digunakan saat pengiriman pengaduan">
                <p class="mt-2 text-muted" style="font-size: 0.875rem; color: var(--bs-secondary-text);">
                    Wajib diisi jika pengaduan dikirimkan dengan identitas pelapor
                </p>
            </div>
            
            <div class="pt-4">
                <button type="submit" 
                        class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Lacak Pengaduan
                </button>
            </div>
        </form>
        
        <div class="mt-6 pt-4 border-top">
            <h3 class="h5 fw-bold mb-3" style="color: var(--bs-text);">Cara Melacak Pengaduan</h3>
            <div class="space-y-4">
                <div class="step-container">
                    <div class="step-icon">
                        <span class="fw-bold" style="color: var(--bs-primary);">1</span>
                    </div>
                    <div>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">Masukkan nomor tiket pengaduan yang Anda terima saat mengirimkan pengaduan</p>
                    </div>
                </div>
                <div class="step-container">
                    <div class="step-icon">
                        <span class="fw-bold" style="color: var(--bs-primary);">2</span>
                    </div>
                    <div>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">Jika Anda mengirimkan pengaduan dengan identitas, masukkan juga email pelapor</p>
                    </div>
                </div>
                <div class="step-container">
                    <div class="step-icon">
                        <span class="fw-bold" style="color: var(--bs-primary);">3</span>
                    </div>
                    <div>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">Klik tombol "Lacak Pengaduan" untuk melihat status terkini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Information Section -->
    <div class="mt-6">
        <div class="text-center mb-6">
            <h2 class="h3 fw-bold mb-3" style="color: var(--bs-text);">Status Pengaduan</h2>
            <p class="text-muted" style="color: var(--bs-secondary-text); max-width: 2xl mx-auto;">
                Penjelasan tentang status yang mungkin muncul pada pengaduan Anda
            </p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="status-card">
                    <div class="mx-auto mb-3" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-clock" style="color: #f59e0b;"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--bs-text);">Menunggu</h3>
                    <p class="text-muted mb-0" style="color: var(--bs-secondary-text);">
                        Pengaduan telah diterima dan menunggu verifikasi awal
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="status-card">
                    <div class="mx-auto mb-3" style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-cog" style="color: #3b82f6;"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--bs-text);">Diproses</h3>
                    <p class="text-muted mb-0" style="color: var(--bs-secondary-text);">
                        Pengaduan sedang dalam proses investigasi dan tindak lanjut
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="status-card">
                    <div class="mx-auto mb-3" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--bs-text);">Selesai</h3>
                    <p class="text-muted mb-0" style="color: var(--bs-secondary-text);">
                        Proses tindak lanjut telah selesai dan hasilnya tersedia
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection