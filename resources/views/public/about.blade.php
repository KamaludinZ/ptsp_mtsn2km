@extends('layouts.public')

@section('title', 'Tentang Kami - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

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
</style>
@endpush

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Tentang <span style="color: var(--bs-primary);">PTSP</span> MTsN 2 Kota Malang
        </h1>
        <p class="lead text-muted">
            Pelayanan Terpadu Satu Pintu yang Transparan, Akuntabel, dan Cepat
        </p>
    </div>

    <!-- About Section -->
    <section class="py-3" aria-labelledby="about-heading">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 id="about-heading" class="h2 fw-bold mb-4">
                    Apa itu <span style="color: var(--bs-primary);">PTSP</span>?
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
                            <div style="width: 48px; height: 48px; background: rgba(20, 83, 45, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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
                            <div style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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
                            <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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

            <div class="col-lg-6 mt-4 mt-lg-0" data-aos="fade-left">
                <div class="p-4 rounded-4" style="background: var(--gradient-primary); color: white;">
                    <h3 class="h2 fw-bold mb-4 text-center text-white">14 Komponen Standar Pelayanan</h3>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-gavel fs-3 mb-2 icon-hover" style="color: #fbbf24;"></i>
                                <p class="small fw-semibold mb-0 component-text">Dasar Hukum</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-list-alt fs-3 mb-2 icon-hover" style="color: #60a5fa;"></i>
                                <p class="small fw-semibold mb-0 component-text">Persyaratan</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-sitemap fs-3 mb-2 icon-hover" style="color: #34d399;"></i>
                                <p class="small fw-semibold mb-0 component-text">Prosedur</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-clock fs-3 mb-2 icon-hover" style="color: #93c5fd;"></i>
                                <p class="small fw-semibold mb-0 component-text">Jangka Waktu</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-money-bill-wave fs-3 mb-2 icon-hover" style="color: #fcd34d;"></i>
                                <p class="small fw-semibold mb-0 component-text">Biaya/Tarif</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center service-component">
                                <i class="fas fa-file-contract fs-3 mb-2 icon-hover" style="color: #fca5a5;"></i>
                                <p class="small fw-semibold mb-0 component-text">Produk Layanan</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="mb-0 text-white">+ 8 Komponen Lainnya</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Performance Stats Section -->
    <section class="py-5 mt-5 performance-section" style="background-color: var(--bs-surface); border-radius: 1rem;" aria-labelledby="stats-heading">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                     style="background-color: rgba(20, 83, 45, 0.1);">
                    <i class="fas fa-chart-line me-2" style="color: var(--bs-primary);"></i>
                    <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Statistik Kinerja</span>
                </div>
                <h2 id="stats-heading" class="h2 fw-bold mb-3">
                    Kinerja <span style="color: var(--bs-primary);">Pelayanan</span> Kami
                </h2>
                <p class="lead text-muted">
                    Transparansi dan Akuntabilitas dalam Setiap Layanan
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm text-center p-4 stat-card" style="background-color: var(--bs-bg);">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 80px; height: 80px; background: var(--gradient-primary);">
                            <i class="fas fa-smile-beam text-white fs-2"></i>
                        </div>
                        <h3 class="display-5 fw-bold mb-2" style="color: var(--bs-primary);">98%</h3>
                        <h4 class="h5 fw-bold mb-2">Tingkat Kepuasan</h4>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-chart-line me-2"></i>Berdasarkan SKM 2025
                        </p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: 98%; background-color: var(--bs-primary);"
                                 aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm text-center p-4 stat-card" style="background-color: var(--bs-bg);">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-secondary-dark) 100%);">
                            <i class="fas fa-clock text-white fs-2"></i>
                        </div>
                        <h3 class="display-5 fw-bold mb-2" style="color: var(--bs-secondary);">2 Hari</h3>
                        <h4 class="h5 fw-bold mb-2">Rata-rata Waktu</h4>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-hourglass-half me-2"></i>Penyelesaian Layanan
                        </p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: 85%; background-color: var(--bs-secondary);"
                                 aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm text-center p-4 stat-card" style="background-color: var(--bs-bg);">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-tasks text-white fs-2"></i>
                        </div>
                        <h3 class="display-5 fw-bold mb-2 text-success">15+</h3>
                        <h4 class="h5 fw-bold mb-2">Jenis Layanan</h4>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-clipboard-check me-2"></i>Sesuai Standar
                        </p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"
                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="card h-100 border-0 shadow-sm text-center p-4 stat-card" style="background-color: var(--bs-bg);">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="fas fa-globe text-white fs-2"></i>
                        </div>
                        <h3 class="display-5 fw-bold mb-2 text-primary">24/7</h3>
                        <h4 class="h5 fw-bold mb-2">Akses Online</h4>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-wifi me-2"></i>Kapan Saja, Dimana Saja
                        </p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;"
                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Bar -->
            <div class="mt-5 p-4 rounded-3 border stats-additional" style="border-color: var(--bs-primary) !important; border-width: 2px !important; border-style: dashed !important; background-color: var(--bs-bg);" data-aos="fade-up" data-aos-delay="500">
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="h2 fw-bold mb-1" style="color: var(--bs-primary);">5000+</div>
                        <div class="text-muted">Layanan Diproses</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="h2 fw-bold mb-1" style="color: var(--bs-secondary);">100%</div>
                        <div class="text-muted">Digitalisasi</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="h2 fw-bold mb-1" style="color: #10b981;">24/7</div>
                        <div class="text-muted">Layanan Online</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="h2 fw-bold mb-1" style="color: #3b82f6;">99.9%</div>
                        <div class="text-muted">Uptime System</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Maklumat Pelayanan Section -->
    <section class="py-5 mt-5" data-aos="fade-up">
        <div class="container">
            <div class="p-5 rounded-4" style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%);">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <img src="{{ asset('images/MAKLUMAT-PELAYANAN-MTSN-2-KOTA-MALANG-31-Januari-2024-768x543.jpg') }}" alt="Maklumat Pelayanan" class="img-fluid rounded-3" style="max-height: 300px; object-fit: cover;">
                    </div>
                    <div class="col-lg-6">
                        <h2 class="h2 fw-bold text-white mb-3">
                            Maklumat Pelayanan
                        </h2>
                        <p class="text-white-50 mb-4">
                            Kami menyediakan informasi layanan yang transparan dan akuntabel sesuai dengan standar pelayanan yang berlaku untuk memastikan kepuasan pengguna layanan.
                        </p>
                        <a href="#" class="btn btn-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>
                            Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Reward and Punishment Section -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="p-5 rounded-4" style="background: linear-gradient(135deg, #0F766E 0%, #10B981 100%);">
                <div class="row align-items-center">
                    <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                        <img src="{{ asset('images/KOMPENSASI-PELAYANAN-MTSN-2-KOTA-MALANG_11zon-1-768x545.jpg') }}" alt="Program Reward and Punishment" class="img-fluid rounded-3" style="max-height: 300px; object-fit: cover;">
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <h2 class="h2 fw-bold text-white mb-3">
                            Program Reward and Punishment
                        </h2>
                        <p class="text-white-50 mb-4">
                            Program penghargaan dan sanksi untuk mendorong kinerja pelayanan yang unggul dan akuntabel sesuai dengan standar pelayanan yang ditetapkan.
                        </p>
                        <a href="#" class="btn btn-light btn-lg">
                            <i class="fas fa-trophy me-2"></i>
                            Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 mt-5 text-center" data-aos="fade-up">
        <div class="p-5 rounded-4" style="background: var(--gradient-primary);">
            <h2 class="h2 fw-bold text-white mb-3">
                Siap Menggunakan Layanan Kami?
            </h2>
            <p class="lead text-white mb-4" style="opacity: 0.9;">
                Akses layanan online 24/7 atau kunjungi loket PTSP kami
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-list me-2"></i>
                    Lihat Layanan
                </a>
                <a href="{{ route('onlineportal.track.ticket.form') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-search me-2"></i>
                    Lacak Tiket
                </a>
            </div>
        </div>
    </section>
</div>
<script>
    // Function to show standards modal
    function showStandardsModal() {
        const modal = document.getElementById('standardsModal');
        modal.classList.remove('hidden');
        // Add event listener to close modal when clicking outside
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeStandardsModal();
            }
        });
    }

    // Function to close standards modal
    function closeStandardsModal() {
        document.getElementById('standardsModal').classList.add('hidden');
    }
</script>
@endsection
