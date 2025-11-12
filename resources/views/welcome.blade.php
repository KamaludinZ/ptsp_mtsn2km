@extends('layouts.public')

@section('title', config('app.name', 'PTSP MTsN 2 Kota Malang') . ' - Beranda')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section id="hero-section" aria-labelledby="hero-heading">
            <!-- Animated Background Elements -->
            <div class="hero-background" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; z-index: 1;">
                <div class="hero-bg-element" style="position: absolute; top: 20%; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
                <div class="hero-bg-element" style="position: absolute; top: 60%; right: 10%; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; animation: float 8s ease-in-out infinite 2s;"></div>
                <div class="hero-bg-element" style="position: absolute; bottom: 20%; left: 20%; width: 80px; height: 80px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 7s ease-in-out infinite 1s;"></div>
            </div>

            <div class="hero-content stagger-fade-in" style="max-width: 900px; padding: 20px; z-index: 2; position: relative;">
                <h1 id="hero-heading" class="hero-title">
                    PTSP MTsN 2 KOTA MALANG
                </h1>

                <div class="hero-subtitle">
                    Pelayanan Terpadu Satu Pintu
                </div>

                <p class="hero-description">
                    Melayani dengan Hati, Cepat, Transparan, dan Akuntabel
                </p>

                <div class="hero-datetime">
                    <div class="time">
                        <i class="fas fa-clock"></i>
                        <span id="current-time"></span>
                    </div>
                    <div class="date">
                        <i class="fas fa-calendar"></i>
                        <span id="current-date"></span>
                    </div>
                </div>

                <div class="hero-buttons">
                    <a href="{{ route('onlineportal.service.catalog') }}" class="hero-btn-primary">
                        <i class="fas fa-th-large"></i>
                        Lihat Semua Layanan
                    </a>

                    <a href="{{ route('onlineportal.track.ticket.form') }}" class="hero-btn-secondary">
                        <i class="fas fa-search"></i>
                        Lacak Status Tiket
                    </a>
                </div>

                <div class="hero-info">
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 08:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Buka</div>
                    </div>
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 15:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Tutup</div>
                    </div>
                    <div class="info-item">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-calendar-week"></i> Senin-Jumat
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Hari Operasional</div>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 2; animation: bounce 2s infinite;">
                <div style="width: 30px; height: 50px; border: 2px solid rgba(255,255,255,0.5); border-radius: 15px; position: relative;">
                    <div style="width: 4px; height: 10px; background: white; border-radius: 2px; position: absolute; top: 8px; left: 50%; transform: translateX(-50%); animation: scroll 2s infinite;"></div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="tentang" aria-labelledby="about-heading">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right">
                        <h2 id="about-heading" class="display-4 fw-bold mb-4">
                            Tentang <span style="color: var(--bs-primary);">PTSP</span> MTsN 2 Kota Malang
                        </h2>
                        <p class="lead mb-4">
                            Pelayanan Terpadu Satu Pintu (PTSP) MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat.
                        </p>
                        <p class="mb-4">
                            Sistem ini dibangun sesuai dengan <strong style="color: var(--bs-primary);">Permen PANRB 15/2014</strong> tentang Pedoman Standar Pelayanan yang menjamin transparansi, akuntabilitas, dan kecepatan pelayanan.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(20, 83, 45, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; shrink-0;">
                                        <i class="fas fa-check-double" style="color: var(--bs-primary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Transparan & Akuntabel</h5>
                                        <p class="text-muted mb-0">Setiap proses layanan dapat dilacak secara real-time dengan sistem monitoring terintegrasi</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; shrink-0;">
                                        <i class="fas fa-clock" style="color: var(--bs-secondary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Cepat & Tepat Waktu</h5>
                                        <p class="text-muted mb-0">Jaminan penyelesaian sesuai standar waktu layanan dengan SLA yang jelas</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; shrink-0;">
                                        <i class="fas fa-mobile-alt" style="color: #10b981;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Mudah Diakses</h5>
                                        <p class="text-muted mb-0">Layanan online 24/7 dan offline di loket PTSP dengan antarmuka yang user-friendly</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="p-4 rounded-4" style="background: var(--gradient-primary); color: white;">
                            <h3 class="h2 fw-bold mb-4 text-center">14 Komponen Standar Pelayanan</h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-gavel fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Dasar Hukum</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-list-alt fs-3 mb-2 text-info icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Persyaratan</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-sitemap fs-3 mb-2 text-success icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Prosedur</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-clock fs-3 mb-2 text-primary icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Jangka Waktu</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-money-bill-wave fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Biaya/Tarif</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-file-contract fs-3 mb-2 text-danger icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Produk Layanan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0">+ 8 Komponen Lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Performance Stats Section -->
        <section style="background-color: var(--bs-gray-50);" aria-labelledby="stats-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-chart-line me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Statistik Kinerja</span>
                    </div>
                    <h2 id="stats-heading" class="display-4 fw-bold mb-3">
                        Kinerja <span style="color: var(--bs-primary);">Pelayanan</span> Kami
                    </h2>
                    <p class="lead text-muted">
                        Transparansi dan Akuntabilitas dalam Setiap Layanan
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Tingkat kepuasan pelanggan 98%">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-primary);">
                                <i class="fas fa-smile-beam text-white"></i>
                            </div>
                            <div class="stat-number">98%</div>
                            <h3 class="stat-label">Tingkat Kepuasan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-chart-line me-2"></i>Berdasarkan SKM 2025
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 98%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Rata-rata waktu penyelesaian 2 hari">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-secondary);">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="stat-number">2 Hari</div>
                            <h3 class="stat-label">Rata-rata Waktu</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-hourglass-half me-2"></i>Penyelesaian Layanan
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 85%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                        <article class="stat-card" tabindex="0" role="article" aria-label="15+ jenis layanan tersedia">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                            <div class="stat-number">15+</div>
                            <h3 class="stat-label">Jenis Layanan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-clipboard-check me-2"></i>Sesuai Standar
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Akses online 24/7">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-globe text-white"></i>
                            </div>
                            <div class="stat-number">24/7</div>
                            <h3 class="stat-label">Akses Online</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-wifi me-2"></i>Kapan Saja, Dimana Saja
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Additional Stats Bar -->
                <div class="mt-5 p-4 rounded-3 border-2 border-dashed" style="border-color: var(--bs-primary); background-color: var(--bs-white);" data-aos="fade-up" data-aos-delay="500">
                    <div class="row text-center">
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-primary);">5000+</div>
                            <div class="text-muted small">Layanan Diproses</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-secondary);">100%</div>
                            <div class="text-muted small">Digitalisasi</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #10b981;">4.8/5</div>
                            <div class="text-muted small">Rating Layanan</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #3b82f6;">99.9%</div>
                            <div class="text-muted small">Uptime</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="layanan" aria-labelledby="services-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-th-large me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Layanan Kami</span>
                    </div>
                    <h2 id="services-heading" class="display-4 fw-bold mb-3">
                        Jenis <span style="color: var(--bs-primary);">Pelayanan</span> Tersedia
                    </h2>
                    <p class="lead text-muted">
                        Melayani berbagai kebutuhan sivitas akademika dan masyarakat
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Layanan Akademik -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <article class="service-card">
                            <div class="service-header service-header-primary">
                                <div class="service-icon">
                                    <i class="fas fa-user-graduate text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Akademik</h3>
                                <p class="service-subtitle">Untuk Siswa & Alumni</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Siswa Aktif</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Legalisir Ijazah & Transkrip</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Berkelakuan Baik</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Wali Murid -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <article class="service-card">
                            <div class="service-header service-header-secondary">
                                <div class="service-icon">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Wali Murid</h3>
                                <p class="service-subtitle">Untuk Orang Tua</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Informasi Akademik Anak</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Tidak Masuk Sekolah</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Panggilan Orang Tua</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Konsultasi BK</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Instansi -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                        <article class="service-card">
                            <div class="service-header service-header-emerald">
                                <div class="service-icon">
                                    <i class="fas fa-briefcase text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Instansi</h3>
                                <p class="service-subtitle">Untuk Mitra Kerja</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Permohonan Kerjasama</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Kegiatan & Penelitian</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Permohonan Data Statistik</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi Instansi</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section style="background-color: var(--bs-gray-50);" aria-labelledby="access-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 id="access-heading" class="display-4 fw-bold mb-3">
                        Akses <span style="color: var(--bs-primary);">Cepat</span>
                    </h2>
                    <p class="lead text-muted">
                        Layanan penting dalam satu klik
                    </p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <a href="{{ route('onlineportal.service.catalog') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto">
                                    <i class="fas fa-file-medical text-white"></i>
                                </div>
                                <h4 class="feature-title">Permohonan Online</h4>
                                <p class="feature-description">
                                    Ajukan layanan secara digital tanpa harus datang ke loket
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Mulai Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <a href="{{ route('onlineportal.track.ticket.form') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: var(--gradient-secondary);">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <h4 class="feature-title">Lacak Tiket</h4>
                                <p class="feature-description">
                                    Cek status permohonan Anda secara real-time
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Track Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <a href="{{ route('public.visitor.book') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                <h4 class="feature-title">Buku Tamu</h4>
                                <p class="feature-description">
                                    Daftar kunjungan fisik ke sekolah
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Daftar Kunjungan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="{{ route('supervision.complaints.dashboard') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                    <i class="fas fa-exclamation-circle text-white"></i>
                                </div>
                                <h4 class="feature-title">Pengaduan</h4>
                                <p class="feature-description">
                                    Sampaikan keluhan atau saran untuk perbaikan layanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Buat Pengaduan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                        <a href="{{ route('supervision.whistleblowing.form') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                                <h4 class="feature-title">Whistleblowing</h4>
                                <p class="feature-description">
                                    Laporkan pelanggaran secara aman dan terlindungi
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Laporkan Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                        <a href="{{ route('supervision.skm.survey') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                    <i class="fas fa-poll text-white"></i>
                                </div>
                                <h4 class="feature-title">Survei SKM</h4>
                                <p class="feature-description">
                                    Bantu kami tingkatkan kualitas pelayanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Isi Survei</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
<script>
    // Function to update time and date
    function updateDateTime() {
        const now = new Date();

        // Format time (HH:MM:SS)
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = `${hours}:${minutes}:${seconds} WIB`;

        // Format date (Day, DD Month YYYY)
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const dateString = `${dayName}, ${day} ${month} ${year}`;

        // Update DOM
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');

        if (timeElement) timeElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }

    // Update immediately when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateDateTime();
        // Update every second
        setInterval(updateDateTime, 1000);
    });
</script>
@endpush
