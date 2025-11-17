@extends('layouts.public')

@section('title', 'Layanan Pengaduan - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Hero Section */
    .visitor-hero {
        background: linear-gradient(135deg, #15803d 0%, #166534 50%, #14532d 100%);
        color: white;
        padding: 3rem 0 2rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 0;
    }

    .visitor-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        z-index: 0;
    }

    .visitor-hero .container {
        position: relative;
        z-index: 1;
    }

    .visitor-hero .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .visitor-hero .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }

    /* Stats Cards */
    .stat-card-visitor {
        background: white;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    .stat-card-visitor:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        border-color: rgba(21, 128, 61, 0.2);
    }

    .stat-card-visitor .stat-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.25rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-card-visitor .stat-value {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .stat-card-visitor .stat-label {
        font-size: 1rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Tabs */
    .nav-tabs-visitor {
        border: none;
        gap: 1rem;
        margin-bottom: -1px;
    }

    .nav-tabs-visitor .nav-link {
        border: 2px solid transparent;
        border-radius: 12px 12px 0 0;
        padding: 1rem 2rem;
        font-weight: 600;
        color: var(--bs-gray-600);
        background: var(--bs-gray-100);
        transition: all 0.3s ease;
    }

    .nav-tabs-visitor .nav-link:hover {
        background: var(--bs-gray-200);
        color: var(--bs-gray-800);
    }

    .nav-tabs-visitor .nav-link.active {
        background: white;
        color: var(--bs-primary);
        border-color: var(--bs-gray-300) var(--bs-gray-300) white;
    }

    /* Form Card */
    .visitor-form-card {
        background: white;
        border-radius: 0 16px 16px 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--bs-gray-200);
        min-height: 400px;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    /* Tab Content */
    .tab-content {
        padding-top: 1rem;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane {
        display: none !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane.active {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane.show {
        display: block !important;
    }

    /* Dark Mode */
    [data-theme="dark"] body {
        background-color: #111827 !important;
    }

    [data-theme="dark"] .stat-card-visitor {
        background: #1f2937 !important;
        border: 1px solid #374151;
    }

    [data-theme="dark"] .stat-card-visitor .stat-value {
        color: white !important;
    }

    [data-theme="dark"] .stat-card-visitor .stat-label {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link {
        background: #1f2937;
        color: #9ca3af;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link.active {
        background: #374151;
        color: white;
        border-color: #4b5563 #4b5563 #374151;
    }

    [data-theme="dark"] .visitor-form-card {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .card {
        background: #1f2937 !important;
        border-color: #374151 !important;
        color: white !important;
    }

    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: white !important;
    }

    [data-theme="dark"] p,
    [data-theme="dark"] .text-muted {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .form-label {
        color: white !important;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] textarea {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
        color: white !important;
    }

    [data-theme="dark"] .form-check-label {
        color: #d1d5db !important;
    }
    .nav-tabs-visitor .nav-link#whistleblowing-alt-tab {
        background: #dc3545; /* Bootstrap red */
        color: white;
        border-color: #dc3545 #dc3545 #dc3545; /* Red border all around when not active */
    }

    .nav-tabs-visitor .nav-link#whistleblowing-alt-tab:hover {
        background: #c82333; /* Slightly darker red on hover */
        color: white;
        border-color: #c82333 #c82333 #c82333;
    }

    .nav-tabs-visitor .nav-link#whistleblowing-alt-tab.active {
        background: #c82333; /* Darker red when active */
        color: white;
        border-color: #c82333 #c82333 white; /* White bottom border when active */
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link#whistleblowing-alt-tab {
        background: #dc3545; /* Bootstrap red */
        color: white;
        border-color: #dc3545 #dc3545 #dc3545;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link#whistleblowing-alt-tab:hover {
        background: #c82333; /* Slightly darker red on hover */
        color: white;
        border-color: #c82333 #c82333 #c82333;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link#whistleblowing-alt-tab.active {
        background: #c82333; /* Darker red when active */
        color: white;
        border-color: #c82333 #c82333 #1f2937; /* Dark mode background for bottom border */
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="visitor-hero">
    <div class="container">
        <div class="text-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white">Beranda</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Pengaduan</li>
                </ol>
            </nav>
            <h1 class="display-4 fw-bold mb-3">Layanan Pengaduan</h1>
            <p class="lead mb-4 opacity-90">Sampaikan keluhan, saran, atau informasi penting terkait pelayanan di MTsN 2 Kota Malang</p>
        </div>

        <!-- Statistics -->
        <div class="row g-4 justify-content-center mt-4">
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%);">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="stat-value">1,247</div>
                    <div class="stat-label">Total Pengaduan</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-user-check text-white"></i>
                    </div>
                    <div class="stat-value">98%</div>
                    <div class="stat-label">Ditindaklanjuti</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div class="stat-value">7</div>
                    <div class="stat-label">Hari Rata-rata</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container complaint-container">
    <!-- Alternative: Direct Tabbed Interface -->
    <div class="card border-0 shadow-sm rounded-4 mb-6">
        <div class="card-header bg-white py-4 border-bottom-0">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-tabs-visitor mb-0" id="complaintTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dumas-alt-tab" data-bs-toggle="tab" data-bs-target="#dumas-alt" type="button" role="tab" aria-controls="dumas-alt" aria-selected="true">
                        <i class="fas fa-comment me-2"></i>Pengaduan Masyarakat
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="whistleblowing-alt-tab" data-bs-toggle="tab" data-bs-target="#whistleblowing-alt" type="button" role="tab" aria-controls="whistleblowing-alt" aria-selected="false">
                        <i class="fas fa-user-secret me-2"></i>Whistleblowing
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="visitor-form-card p-4">
            <!-- Tab panes -->
            <div class="tab-content" id="complaintTabContentAlt">
                <!-- Dumas Tab -->
                <div class="tab-pane fade show active" id="dumas-alt" role="tabpanel" aria-labelledby="dumas-alt-tab">
                    <form action="{{ route('supervision.complaint.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="complaint_type" value="complaint">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="reporter_name_alt" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>Nama Pelapor <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="reporter_name_alt" name="reporter_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reporter_email_alt" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email Pelapor <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="reporter_email_alt" name="reporter_email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reporter_phone_alt" class="form-label fw-semibold">
                                    <i class="fas fa-phone me-2 text-primary"></i>Nomor Telepon
                                </label>
                                <input type="tel" class="form-control" id="reporter_phone_alt" name="reporter_phone">
                            </div>
                            <div class="col-md-6">
                                <label for="complaint_date_alt" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Tanggal Kejadian <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="complaint_date_alt" name="complaint_date" required>
                            </div>
                            <div class="col-12">
                                <label for="complaint_title_alt" class="form-label fw-semibold">
                                    <i class="fas fa-heading me-2 text-primary"></i>Judul Pengaduan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="complaint_title_alt" name="complaint_title" required>
                            </div>
                            <div class="col-12">
                                <label for="complaint_description_alt" class="form-label fw-semibold">
                                    <i class="fas fa-file-alt me-2 text-primary"></i>Isi Pengaduan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="complaint_description_alt" name="complaint_description" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <label for="attachment_alt" class="form-label fw-semibold">
                                    <i class="fas fa-paperclip me-2 text-primary"></i>Lampiran (jika ada)
                                </label>
                                <input type="file" class="form-control" id="attachment_alt" name="attachment">
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pengaduan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Whistleblowing Tab -->
                <div class="tab-pane fade" id="whistleblowing-alt" role="tabpanel" aria-labelledby="whistleblowing-alt-tab">
                    <div class="alert alert-danger mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Whistleblowing</strong> - Gunakan formulir ini untuk melaporkan pelanggaran serius seperti korupsi, penipuan, suap, atau penyalahgunaan wewenang yang terjadi di dalam organisasi.
                    </div>
                    
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-4 mb-4">
                        <div class="flex flex-col md:flex-row items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-red-100 rounded-full p-3">
                                    <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 mb-1">
                                    <i class="fas fa-lock me-2"></i> Kerahasiaan Terjamin
                                </h5>
                                <p class="text-gray-700">
                                    Kami menjamin kerahasiaan identitas pelapor dan melindungi dari segala bentuk represaliasi. Laporan Anda akan ditangani dengan profesional dan rahasia.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('supervision.complaint.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="complaint_type" value="whistleblowing">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="violation_category_alt" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-danger"></i>Kategori Pelanggaran <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="violation_category_alt" name="violation_category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="corruption">Korupsi</option>
                                    <option value="gratification">Gratifikasi</option>
                                    <option value="nepotism">Nepotisme/Kolusi</option>
                                    <option value="misconduct">Pelanggaran Etika</option>
                                    <option value="misuse">Penyalahgunaan Wewenang</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="incident_date_alt" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-danger"></i>Tanggal Kejadian
                                </label>
                                <input type="date" class="form-control" id="incident_date_alt" name="incident_date">
                            </div>
                            <div class="col-12">
                                <label for="incident_title_alt" class="form-label fw-semibold">
                                    <i class="fas fa-heading me-2 text-danger"></i>Judul Laporan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="incident_title_alt" name="complaint_title" required placeholder="Ringkasan Pelanggaran">
                            </div>
                            <div class="col-12">
                                <label for="incident_description_alt" class="form-label fw-semibold">
                                    <i class="fas fa-file-alt me-2 text-danger"></i>Deskripsi Kejadian <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="incident_description_alt" name="complaint_description" rows="5" required placeholder="Jelaskan secara detail kejadian pelanggaran..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="evidence_alt" class="form-label fw-semibold">
                                    <i class="fas fa-paperclip me-2 text-danger"></i>Bukti Pendukung
                                </label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="evidence_alt" name="attachment" multiple>
                                    <label class="input-group-text" for="evidence_alt">Unggah File</label>
                                </div>
                                <div class="form-text">
                                    Anda dapat mengunggah beberapa file sebagai bukti pendukung (PNG, JPG, PDF, DOCX - Maks 10MB)
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="anonymous_report_alt" name="anonymous" value="1">
                                    <label class="form-check-label fw-semibold" for="anonymous_report_alt">
                                        <i class="fas fa-user-secret me-2 text-danger"></i>Laporkan secara anonim
                                    </label>
                                </div>
                                
                                <div id="reporter_identity_section_alt">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="reporter_name_whistle_alt" class="form-label fw-semibold">
                                                <i class="fas fa-user me-2 text-danger"></i>Nama Lengkap
                                            </label>
                                            <input type="text" class="form-control" id="reporter_name_whistle_alt" name="reporter_name">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reporter_position_alt" class="form-label fw-semibold">
                                                <i class="fas fa-briefcase me-2 text-danger"></i>Jabatan/Posisi
                                            </label>
                                            <input type="text" class="form-control" id="reporter_position_alt" name="reporter_position" placeholder="Contoh: Pegawai, Kontraktor, dll">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="reporter_email_whistle_alt" class="form-label fw-semibold">
                                                <i class="fas fa-envelope me-2 text-danger"></i>Email
                                            </label>
                                            <input type="email" class="form-control" id="reporter_email_whistle_alt" name="reporter_email">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reporter_phone_whistle_alt" class="form-label fw-semibold">
                                                <i class="fas fa-phone me-2 text-danger"></i>Nomor Telepon
                                            </label>
                                            <input type="tel" class="form-control" id="reporter_phone_whistle_alt" name="reporter_phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-bullhorn me-2"></i> Kirim Laporan Rahasia
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Other Information -->
    <div class="card shadow-lg border-0 mt-5">
        <div class="card-body p-4">
            <!-- Information Section -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5 fw-bold mb-3">
                                <i class="fas fa-shield-alt me-2 text-primary"></i>Perlindungan Whistleblower
                            </h3>
                            <p class="text-muted mb-0">
                                Kami menjamin kerahasiaan identitas pelapor dalam kasus whistleblowing dan melindungi dari segala bentuk represaliasi.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5 fw-bold mb-3">
                                <i class="fas fa-clock me-2 text-primary"></i>Waktu Respons
                            </h3>
                            <p class="text-muted mb-0">
                                Pengaduan akan ditindaklanjuti dalam waktu maksimal 5 hari kerja sejak tanggal diterima.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lapor.go.id Information -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <img src="{{ asset('images/Simf60FF_SP4N-Lapor.png') }}"
                                 alt="SP4N LAPOR!"
                                 class="img-fluid"
                                 style="max-height: 80px;">
                        </div>
                        <div class="col-md-9 text-md-start">
                            <h3 class="h5 fw-bold mb-2">
                                <i class="fas fa-megaphone me-2 text-primary"></i>Lapor Pengaduan Nasional
                            </h3>
                            <p class="mb-2 text-muted">
                                Selain melaporkan pengaduan kepada kami, Anda juga dapat menyampaikan aspirasi dan pengaduan pelayanan publik secara nasional melalui:
                            </p>
                            <a href="https://www.lapor.go.id/"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Kunjungi LAPOR.GO.ID
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Section -->
            <div class="card mb-4 bg-success-subtle">
                <div class="card-body">
                    <h2 class="h3 fw-bold mb-4 text-center">Lacak Status Pengaduan Anda</h2>
                    <p class="text-muted mb-4 text-center">Ketahui status terkini dari pengaduan yang telah Anda sampaikan</p>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <form method="POST" action="{{ route('supervision.complaint.track') }}" class="space-y-4">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="complaint_number" class="form-label fw-semibold">
                                        <i class="fas fa-ticket-alt me-2 text-primary"></i>Nomor Tiket Pengaduan
                                    </label>
                                    <input type="text" 
                                           id="complaint_number" 
                                           name="complaint_number" 
                                           required
                                           class="form-control"
                                           placeholder="Masukkan nomor tiket">
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label for="reporter_email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email Pelapor (Opsional)
                                    </label>
                                    <input type="email" 
                                           id="reporter_email" 
                                           name="reporter_email" 
                                           class="form-control"
                                           placeholder="Email yang digunakan saat pengiriman">
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" 
                                            class="btn btn-primary btn-lg">
                                        <i class="fas fa-search me-2"></i> Lacak Pengaduan
                                    </button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('supervision.complaint.track.form') }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-2"></i> Buka halaman pelacakan lengkap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Standards Compliance Note -->
            <div class="card">
                <div class="card-body p-4 border-start border-primary border-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-primary info-icon-large" style="font-size: 2.5rem;"></i>
                        </div>
                        <div class="ms-4">
                            <h4 class="h5 fw-bold mb-2">
                                Kepatuhan terhadap Standar Pelayanan
                            </h4>
                            <p class="mb-3 text-muted">
                                Layanan pengaduan ini diselaraskan dengan Peraturan Menteri PANRB Nomor 15 Tahun 2014 tentang Pedoman Pelayanan Publik.
                            </p>
                            <button onclick="showStandardsModal()" class="btn btn-outline-primary">
                                <i class="fas fa-list-check me-2"></i> Lihat 14 Komponen Standar Pelayanan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Standards Modal -->
<div id="standardsModal" class="modal fade standards-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">14 Komponen Standar Pelayanan</h5>
                <button type="button" class="btn-close" onclick="closeStandardsModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="space-y-3">
                    <!-- Pengaduan Layanan is the 6th component in the service -->
                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            6. Penanganan Pengaduan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Proses penanganan keluhan dan pengaduan dari masyarakat melalui sistem terpadu.
                        </p>
                    </div>

                    <!-- Other standard components -->
                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            1. Dasar Hukum
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Landasan hukum penyelenggaraan pelayanan PTSP MTsN 2 Kota Malang sesuai peraturan perundang-undangan yang berlaku.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            2. Persyaratan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Dokumen dan persyaratan yang harus dipenuhi oleh pemohon untuk mendapatkan pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            3. Sistem, Mekanisme, Prosedur
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Alur dan tata cara pelayanan dari mulai pengajuan hingga selesai.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            4. Jangka Waktu Penyelesaian
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Estimasi waktu yang dibutuhkan untuk menyelesaikan setiap pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            5. Biaya/Tarif
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Informasi biaya atau tarif pelayanan yang berlaku (jika ada).
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            7. Produk Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Hasil akhir dari pelayanan yang diberikan kepada pemohon.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            8. Sarana, Prasarana, dan Sistem
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Fasilitas dan sistem pendukung penyelenggaraan pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            9. Jumlah dan Layanan Pelaksana
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jumlah petugas yang tersedia untuk setiap layanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            10. Jaminan Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jaminan yang diberikan kepada pemohon terkait kualitas dan waktu pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            11. Jaminan Keamanan dan Keselamatan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jaminan keamanan data dan informasi pemohon.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            12. Evaluasi Kinerja
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Mekanisme evaluasi kinerja pelayanan secara berkala.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            13. Penetapan Standar Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Mekanisme penetapan standar pelayanan yang harus dipenuhi.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            14. Sistem Pengelolaan Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Sistem pengelolaan pelayanan yang terintegrasi dan terdokumentasi secara baik.
                        </p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <p class="mb-0 text-center" style="color: var(--bs-secondary-text);">
                        <i class="fas fa-info-circle me-2"></i>
                        Untuk informasi lebih lengkap, silakan hubungi PTSP MTsN 2 Kota Malang.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Function to show standards modal
    function showStandardsModal() {
        const modal = document.getElementById('standardsModal');
        modal.classList.add('show', 'd-block');
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        // Add backdrop
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');
    }

    // Function to close standards modal
    function closeStandardsModal() {
        const modal = document.getElementById('standardsModal');
        modal.classList.remove('show', 'd-block');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
    }
    
    // Handle anonymous checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const anonymousCheckbox = document.getElementById('anonymous_report_alt');
        const identityFields = document.getElementById('reporter_identity_section_alt');
        
        if (anonymousCheckbox && identityFields) {
            // Initially hide identity fields if anonymous is checked
            if (anonymousCheckbox.checked) {
                identityFields.style.display = 'none';
            }
            
            // Toggle identity fields visibility when checkbox changes
            anonymousCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    identityFields.style.display = 'none';
                } else {
                    identityFields.style.display = 'block';
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var triggerTabList = [].slice.call(document.querySelectorAll('#complaintTab button'))
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    });
</script>
@endpush
