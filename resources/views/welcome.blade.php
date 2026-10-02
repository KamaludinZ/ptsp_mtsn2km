<!-- MARKER: welcome -->
@extends('layouts.public')

@section('title', config('app.name', 'PTSP MTsN 2 Kota Malang') . ' - Beranda')

@push('styles')
    @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
        @vite('resources/css/home.css')
    @else
        {!! App\Helpers\AssetHelper::css('resources/css/home.css') !!}
    @endif
@endpush

@section('content')
    <!-- Main Content -->
    <div class="home-page">
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
                                    <div class="shrink-0" style="width: 48px; height: 48px; background: rgba(20, 83, 45, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
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
                                    <div class="shrink-0" style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
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
                                    <div class="shrink-0" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
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
                    <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3 section-eyebrow"
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

                @php
                    $fmt = fn ($value, $suffix = '') => $value === null ? '–' : number_format($value, is_float($value) ? 1 : 0, ',', '.') . $suffix;
                    $ikmLabel = match (true) {
                        $stats['ikm'] === null => 'Belum ada data survei',
                        $stats['ikm'] >= 88.31 => 'Mutu A (Sangat Baik)',
                        $stats['ikm'] >= 76.61 => 'Mutu B (Baik)',
                        $stats['ikm'] >= 65 => 'Mutu C (Kurang Baik)',
                        default => 'Mutu D (Tidak Baik)',
                    };
                    $cards = [
                        ['icon' => 'fa-smile-beam', 'bg' => 'var(--gradient-primary)', 'value' => $fmt($stats['ikm']), 'label' => 'Indeks Kepuasan (IKM)', 'note' => $ikmLabel, 'bar' => $stats['ikm']],
                        ['icon' => 'fa-clock', 'bg' => 'var(--gradient-secondary)', 'value' => $stats['average_days'] === null ? '–' : $stats['average_days'] . ' Hari', 'label' => 'Rata-rata Penyelesaian', 'note' => $stats['average_days'] === null ? 'Belum ada tiket selesai' : 'Dari tiket yang telah selesai', 'bar' => null],
                        ['icon' => 'fa-tasks', 'bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'value' => $fmt($stats['services']), 'label' => 'Jenis Layanan Aktif', 'note' => 'Sesuai standar pelayanan', 'bar' => null],
                        ['icon' => 'fa-globe', 'bg' => 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)', 'value' => '24/7', 'label' => 'Akses Online', 'note' => 'Ajukan & lacak kapan saja', 'bar' => null],
                    ];
                @endphp

                <!-- Stats Grid (live data, cached 10 minutes) -->
                <div class="row g-4">
                    @foreach ($cards as $card)
                        <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                            <article class="stat-card h-100">
                                <div class="feature-icon mb-3 mx-auto" style="background: {{ $card['bg'] }};" aria-hidden="true">
                                    <i class="fas {{ $card['icon'] }} text-white"></i>
                                </div>
                                <div class="stat-number">{{ $card['value'] }}</div>
                                <h3 class="stat-label">{{ $card['label'] }}</h3>
                                <p class="text-muted small mb-0">{{ $card['note'] }}</p>
                                @if ($card['bar'] !== null)
                                    <div class="stat-progress mt-3" role="progressbar" aria-label="{{ $card['label'] }}"
                                         aria-valuenow="{{ $card['bar'] }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="stat-progress-bar" style="width: {{ min(100, $card['bar']) }}%;"></div>
                                    </div>
                                @endif
                            </article>
                        </div>
                    @endforeach
                </div>

                <!-- Additional Stats Bar -->
                <div class="stats-strip mt-5 p-4 rounded-3 row text-center mx-0 mb-0" data-aos="fade-up">
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="display-6 fw-bold mb-0 stats-strip__value stats-strip__value--green">{{ $fmt($stats['tickets']) }}</div>
                        <div class="text-muted small fw-normal">Permohonan Masuk</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="display-6 fw-bold mb-0 stats-strip__value stats-strip__value--orange">{{ $fmt($stats['completed_percent'], '%') }}</div>
                        <div class="text-muted small fw-normal">Permohonan Selesai</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-0 stats-strip__value stats-strip__value--emerald">{{ $fmt($stats['respondents']) }}</div>
                        <div class="text-muted small fw-normal">Responden Survei</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-bold mb-0 stats-strip__value stats-strip__value--blue">{{ $fmt($stats['closed']) }}</div>
                        <div class="text-muted small fw-normal">Permohonan Ditutup</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="layanan" aria-labelledby="services-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3 section-eyebrow"
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

        <!-- How to Use PTSP Section -->
        <section id="cara-menggunakan" aria-labelledby="how-to-use-heading" style="background-color: var(--bs-gray-50);">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3 section-eyebrow"
                         style="background-color: rgba(234, 88, 12, 0.1);">
                        <i class="fas fa-question-circle me-2" style="color: var(--bs-secondary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-secondary);">Panduan Penggunaan</span>
                    </div>
                    <h2 id="how-to-use-heading" class="display-4 fw-bold mb-3">
                        Cara Menggunakan <span style="color: var(--bs-primary);">Aplikasi PTSP</span>
                    </h2>
                    <p class="lead text-muted">
                        Ikuti langkah mudah untuk mendapatkan pelayanan terbaik kami
                    </p>
                </div>

                @php
                    // Alur sesuai docs/rancangan_app.md: portal (Modul 3-6) dan loket (Modul 1-2),
                    // lalu diproses TU (Modul 7), disetujui pimpinan (Modul 8), hasil (Modul 9), survei (Modul 11).
                    $guides = [
                        'online' => [
                            'tone' => 'primary',
                            'icon' => 'fa-laptop',
                            'title' => 'Pelayanan Online',
                            'intro' => 'Ajukan layanan kapan saja dan dari mana saja melalui portal.',
                            'steps' => [
                                ['fa-user-plus', 'Daftar akun atau masuk', 'Buat akun dengan email dan nomor WhatsApp aktif. Siswa, guru, dan pegawai memakai kode registrasi dari madrasah.', route('register'), 'Daftar akun'],
                                ['fa-list-check', 'Pilih layanan', 'Buka katalog, lalu baca persyaratan, biaya, dan jangka waktu penyelesaian setiap layanan.', route('onlineportal.service.catalog'), 'Lihat katalog'],
                                ['fa-file-arrow-up', 'Isi formulir & unggah berkas', 'Lengkapi formulir permohonan dan unggah dokumen persyaratan (PDF atau foto).', null, null],
                                ['fa-ticket', 'Terima nomor tiket', 'Nomor tiket dan perkiraan tanggal selesai tampil di layar serta di dashboard Anda.', null, null],
                                ['fa-magnifying-glass', 'Pantau status', 'Permohonan diverifikasi petugas TU dan disetujui pimpinan. Pantau tahapannya dari dashboard atau fitur Lacak Tiket.', route('onlineportal.track.ticket.form'), 'Lacak tiket'],
                                ['fa-file-circle-check', 'Terima hasil layanan', 'Dokumen digital dapat diunduh dari dashboard. Dokumen fisik diambil di loket PTSP.', null, null],
                                ['fa-star', 'Isi survei kepuasan', 'Beri penilaian SKM & SPAK agar pelayanan terus membaik.', route('survey.form'), 'Isi survei'],
                            ],
                        ],
                        'offline' => [
                            'tone' => 'secondary',
                            'icon' => 'fa-building',
                            'title' => 'Pelayanan Offline',
                            'intro' => 'Datang langsung ke loket PTSP untuk dibantu petugas secara tatap muka.',
                            'steps' => [
                                ['fa-door-open', 'Datang ke loket PTSP', 'Kunjungi loket PTSP MTsN 2 Kota Malang pada jam layanan.', null, null],
                                ['fa-clipboard-question', 'Sampaikan keperluan', 'Petugas menanyakan keperluan Anda. Tamu dicatat di buku tamu, sedangkan pemohon diarahkan ke pendaftaran layanan.', null, null],
                                ['fa-folder-open', 'Serahkan data & berkas', 'Petugas mengisi permohonan atas nama Anda dan memindai dokumen persyaratan. Siapkan nomor WhatsApp atau email aktif.', null, null],
                                ['fa-receipt', 'Terima tanda terima', 'Anda menerima nomor tiket beserta perkiraan tanggal selesai.', null, null],
                                ['fa-magnifying-glass', 'Pantau status', 'Gunakan nomor tiket untuk melacak proses tanpa perlu datang kembali.', route('onlineportal.track.ticket.form'), 'Lacak tiket'],
                                ['fa-box-open', 'Ambil hasil di loket', 'Setelah selesai, hasil layanan diserahkan kepada Anda di loket PTSP.', null, null],
                                ['fa-star', 'Isi survei kepuasan', 'Beri penilaian SKM & SPAK agar pelayanan terus membaik.', route('survey.form'), 'Isi survei'],
                            ],
                        ],
                    ];
                @endphp

                <div class="row g-4 align-items-stretch">
                    @foreach ($guides as $key => $guide)
                        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <article class="guide-card guide-{{ $guide['tone'] }} h-100" aria-labelledby="guide-{{ $key }}">
                                <header class="guide-header">
                                    <span class="guide-header-icon" aria-hidden="true"><i class="fas {{ $guide['icon'] }}"></i></span>
                                    <div>
                                        <h3 id="guide-{{ $key }}" class="guide-title">{{ $guide['title'] }}</h3>
                                        <p class="guide-intro">{{ $guide['intro'] }}</p>
                                    </div>
                                    <span class="guide-count">{{ count($guide['steps']) }} langkah</span>
                                </header>

                                <ol class="guide-steps">
                                    @foreach ($guide['steps'] as [$icon, $title, $text, $url, $label])
                                        <li class="guide-step">
                                            <span class="guide-step-number" aria-hidden="true">{{ $loop->iteration }}</span>
                                            <div class="guide-step-body">
                                                <h4 class="guide-step-title">
                                                    <span class="visually-hidden">Langkah {{ $loop->iteration }}: </span>{{ $title }}
                                                    <i class="fas {{ $icon }} guide-step-icon" aria-hidden="true"></i>
                                                </h4>
                                                <p class="guide-step-text">{{ $text }}</p>
                                                @if ($url)
                                                    <a href="{{ $url }}" class="guide-step-link">{{ $label }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </article>
                        </div>
                    @endforeach
                </div>

                <p class="text-center text-muted mt-4 mb-0" data-aos="fade-up">
                    <i class="fas fa-circle-info me-1" aria-hidden="true"></i>
                    Setiap permohonan diverifikasi petugas TU dan disetujui pimpinan sesuai standar pelayanan.
                    Ada kendala? <a href="{{ route('supervision.complaints.dashboard') }}">Sampaikan pengaduan atau saran</a>.
                </p>
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
                        <a href="{{ route('supervision.complaints.dashboard', ['tab' => 'whistleblowing']) }}" class="text-decoration-none">
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
                        <a href="{{ route('survey.form') }}" class="text-decoration-none">
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
    </div>
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
