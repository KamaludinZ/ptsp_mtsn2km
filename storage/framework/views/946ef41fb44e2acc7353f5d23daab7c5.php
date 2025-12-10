<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="emerald">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'PTSP MTsN 2 Kota Malang')); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">

    <!-- Aset LOCAL via Vite (Tailwind + DaisyUI + Font Awesome + AOS) -->
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::css('resources/css/home.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

    <?php endif; ?>
</head>
<body class="antialiased">
    <!-- Top Bar -->
    <div class="bg-base-300 border-b border-base-content/10">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-4 py-2 text-xs overflow-x-auto">
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-phone w-3"></i>
                    <span>(0341) 711500</span>
                </div>
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-envelope w-3"></i>
                    <span>mtsnmalang2adm@gmail.com</span>
                </div>
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fab fa-whatsapp w-3 text-success"></i>
                    <span>0851 8336 7500</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Main Content -->
    <main class="overflow-x-hidden">
        <!-- Hero Section -->
        <section class="relative min-h-[90vh] flex items-center bg-gradient-to-br from-primary to-secondary">
            <div class="absolute inset-0 bg-black/20"></div>

            <div class="container mx-auto px-4 py-20 relative z-10">
                <div class="max-w-4xl mx-auto text-center text-white">
                    <!-- Badge -->
                    <div data-aos="fade-down" data-aos-duration="600">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
                            <i class="fas fa-award text-yellow-300"></i>
                            <span class="font-semibold">Permen PANRB 15/2014</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-6 leading-tight" data-aos="fade-up" data-aos-delay="100">
                        PTSP MTsN 2<br class="hidden sm:block">
                        Kota Malang
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl md:text-2xl font-semibold mb-4" data-aos="fade-up" data-aos-delay="200">
                        Pelayanan Terpadu Satu Pintu
                    </p>

                    <p class="text-base md:text-lg mb-10 opacity-90" data-aos="fade-up" data-aos-delay="300">
                        Melayani dengan Hati, Cepat, Transparan, dan Akuntabel
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12" data-aos="fade-up" data-aos-delay="400">
                        <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-warning btn-lg gap-2">
                            <i class="fas fa-th-large"></i>
                            Katalog Layanan
                        </a>
                        <a href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" class="btn btn-outline btn-lg gap-2 text-white border-white hover:bg-white hover:text-primary">
                            <i class="fas fa-search"></i>
                            Lacak Tiket
                        </a>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" data-aos="fade-up" data-aos-delay="500">
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="text-3xl font-bold text-yellow-300">98%</div>
                            <div class="text-sm opacity-90">Kepuasan Layanan</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="text-3xl font-bold text-yellow-300">15+</div>
                            <div class="text-sm opacity-90">Jenis Layanan</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="text-3xl font-bold text-yellow-300">24/7</div>
                            <div class="text-sm opacity-90">Akses Online</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
                <i class="fas fa-chevron-down text-white text-2xl"></i>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-20 bg-base-100">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <!-- Left Content -->
                        <div data-aos="fade-right">
                            <div class="badge badge-primary badge-lg mb-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Tentang Kami
                            </div>

                            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                                Pelayanan Terpadu <span class="text-primary">Satu Pintu</span>
                            </h2>

                            <p class="text-lg mb-6 leading-relaxed opacity-80">
                                PTSP MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat.
                            </p>

                            <p class="mb-8 leading-relaxed opacity-80">
                                Sistem ini dibangun sesuai dengan <strong class="text-primary">Permen PANRB 15/2014</strong> tentang Pedoman Standar Pelayanan yang menjamin transparansi, akuntabilitas, dan kecepatan pelayanan.
                            </p>

                            <!-- Features -->
                            <div class="space-y-4">
                                <div class="flex gap-4 items-start">
                                    <div class="shrink-0 w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-check-double text-primary text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg mb-1">Transparan & Akuntabel</h3>
                                        <p class="text-sm opacity-70">Monitoring real-time untuk semua proses layanan</p>
                                    </div>
                                </div>

                                <div class="flex gap-4 items-start">
                                    <div class="shrink-0 w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-clock text-secondary text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg mb-1">Cepat & Tepat Waktu</h3>
                                        <p class="text-sm opacity-70">Jaminan penyelesaian sesuai SLA yang jelas</p>
                                    </div>
                                </div>

                                <div class="flex gap-4 items-start">
                                    <div class="shrink-0 w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-mobile-alt text-success text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg mb-1">Mudah Diakses</h3>
                                        <p class="text-sm opacity-70">Online 24/7 dan offline di loket PTSP</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Content -->
                        <div data-aos="fade-left">
                            <div class="card bg-gradient-to-br from-primary to-primary-focus text-primary-content shadow-2xl">
                                <div class="card-body p-8">
                                    <h3 class="text-2xl font-bold text-center mb-6">
                                        14 Komponen<br>Standar Pelayanan
                                    </h3>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-gavel text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Dasar Hukum</p>
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-list-alt text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Persyaratan</p>
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-sitemap text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Prosedur</p>
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-clock text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Jangka Waktu</p>
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-money-bill-wave text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Biaya/Tarif</p>
                                        </div>
                                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center hover:bg-white/30 transition">
                                            <i class="fas fa-file-contract text-3xl mb-2 text-white"></i>
                                            <p class="text-sm font-semibold">Produk Layanan</p>
                                        </div>
                                    </div>

                                    <div class="text-center mt-6 opacity-90">
                                        <p class="text-sm">+ 8 Komponen Lainnya</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20 bg-base-200">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-12" data-aos="fade-up">
                        <div class="badge badge-primary badge-lg mb-4">
                            <i class="fas fa-chart-line mr-2"></i>
                            Statistik Kinerja
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">
                            Kinerja <span class="text-primary">Pelayanan</span> Kami
                        </h2>
                        <p class="text-lg opacity-70">
                            Transparansi dan Akuntabilitas dalam Setiap Layanan
                        </p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="card bg-base-100 shadow-lg" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-smile text-primary text-3xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-primary">98%</h3>
                                <p class="text-sm font-semibold">Kepuasan</p>
                                <p class="text-xs opacity-70">SKM 2025</p>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow-lg" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-clock text-secondary text-3xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-secondary">2 Hari</h3>
                                <p class="text-sm font-semibold">Rata-rata</p>
                                <p class="text-xs opacity-70">Penyelesaian</p>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow-lg" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-success/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-clipboard-check text-success text-3xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-success">15+</h3>
                                <p class="text-sm font-semibold">Jenis Layanan</p>
                                <p class="text-xs opacity-70">Sesuai Standar</p>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow-lg" data-aos="fade-up" data-aos-delay="400">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-info/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-wifi text-info text-3xl"></i>
                                </div>
                                <h3 class="text-3xl font-bold text-info">24/7</h3>
                                <p class="text-sm font-semibold">Akses Online</p>
                                <p class="text-xs opacity-70">Selalu Siap</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Stats -->
                    <div class="card bg-base-100 shadow-lg" data-aos="fade-up" data-aos-delay="500">
                        <div class="card-body p-0">
                            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-base-300">
                                <div class="p-6 text-center">
                                    <div class="text-3xl font-bold text-primary">5,000+</div>
                                    <div class="text-sm opacity-70">Layanan Diproses</div>
                                </div>
                                <div class="p-6 text-center">
                                    <div class="text-3xl font-bold text-secondary">100%</div>
                                    <div class="text-sm opacity-70">Digitalisasi</div>
                                </div>
                                <div class="p-6 text-center">
                                    <div class="text-3xl font-bold text-success">4.8/5</div>
                                    <div class="text-sm opacity-70">Rating</div>
                                </div>
                                <div class="p-6 text-center">
                                    <div class="text-3xl font-bold text-info">99.9%</div>
                                    <div class="text-sm opacity-70">Uptime</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-20 bg-base-100">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-12" data-aos="fade-up">
                        <div class="badge badge-primary badge-lg mb-4">
                            <i class="fas fa-th-large mr-2"></i>
                            Layanan Kami
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">
                            Jenis <span class="text-primary">Pelayanan</span> Tersedia
                        </h2>
                        <p class="text-lg opacity-70">
                            Melayani berbagai kebutuhan sivitas akademika dan masyarakat
                        </p>
                    </div>

                    <!-- Service Cards -->
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Card 1 -->
                        <div class="card bg-base-100 border border-base-300 hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center shrink-0">
                                        <i class="fas fa-user-graduate text-primary text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">Layanan Akademik</h3>
                                        <p class="text-xs opacity-70">Siswa & Alumni</p>
                                    </div>
                                </div>
                                <ul class="space-y-2 mb-4">
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Keterangan Siswa Aktif</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Legalisir Ijazah & Transkrip</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Rekomendasi</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Berkelakuan Baik</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary btn-block btn-sm">
                                    Lihat Detail
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="card bg-base-100 border border-base-300 hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-14 h-14 bg-secondary/10 rounded-2xl flex items-center justify-center shrink-0">
                                        <i class="fas fa-users text-secondary text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">Layanan Wali Murid</h3>
                                        <p class="text-xs opacity-70">Orang Tua</p>
                                    </div>
                                </div>
                                <ul class="space-y-2 mb-4">
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Informasi Akademik Anak</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Izin Tidak Masuk Sekolah</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Panggilan Orang Tua</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Konsultasi BK</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary btn-block btn-sm">
                                    Lihat Detail
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="card bg-base-100 border border-base-300 hover:shadow-xl transition-shadow" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-body">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-14 h-14 bg-accent/10 rounded-2xl flex items-center justify-center shrink-0">
                                        <i class="fas fa-briefcase text-accent text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">Layanan Instansi</h3>
                                        <p class="text-xs opacity-70">Mitra Kerja</p>
                                    </div>
                                </div>
                                <ul class="space-y-2 mb-4">
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Permohonan Kerjasama</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Izin Kegiatan & Penelitian</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Permohonan Data Statistik</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm">
                                        <i class="fas fa-check text-success mt-0.5"></i>
                                        <span>Surat Rekomendasi Instansi</span>
                                    </li>
                                </ul>
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary btn-block btn-sm">
                                    Lihat Detail
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section class="py-20 bg-base-200">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-12" data-aos="fade-up">
                        <div class="badge badge-primary badge-lg mb-4">
                            <i class="fas fa-bolt mr-2"></i>
                            Akses Cepat
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">
                            Layanan Penting dalam <span class="text-primary">Satu Klik</span>
                        </h2>
                        <p class="text-lg opacity-70">
                            Akses mudah ke semua layanan PTSP
                        </p>
                    </div>

                    <!-- Access Cards -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-file-alt text-primary text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Permohonan Online</h3>
                                <p class="text-sm opacity-70 mb-4">Ajukan layanan digital tanpa ke loket</p>
                                <div class="badge badge-primary">Mulai Sekarang →</div>
                            </div>
                        </a>

                        <a href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-search text-secondary text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Lacak Tiket</h3>
                                <p class="text-sm opacity-70 mb-4">Cek status permohonan real-time</p>
                                <div class="badge badge-secondary">Track Sekarang →</div>
                            </div>
                        </a>

                        <a href="<?php echo e(route('public.visitor.book')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-accent/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-book text-accent text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Buku Tamu</h3>
                                <p class="text-sm opacity-70 mb-4">Daftar kunjungan fisik</p>
                                <div class="badge badge-accent">Daftar Sekarang →</div>
                            </div>
                        </a>

                        <a href="<?php echo e(route('supervision.complaints.dashboard')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="400">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-error/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-exclamation-circle text-error text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Pengaduan</h3>
                                <p class="text-sm opacity-70 mb-4">Sampaikan keluhan atau saran</p>
                                <div class="badge badge-error">Buat Pengaduan →</div>
                            </div>
                        </a>

                        <a href="<?php echo e(route('supervision.whistleblowing.form')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="500">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-warning/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-bell text-warning text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Whistleblowing</h3>
                                <p class="text-sm opacity-70 mb-4">Laporkan pelanggaran aman</p>
                                <div class="badge badge-warning">Laporkan →</div>
                            </div>
                        </a>

                        <a href="<?php echo e(route('supervision.skm.survey')); ?>" class="card bg-base-100 hover:shadow-xl transition-all hover:-translate-y-1" data-aos="fade-up" data-aos-delay="600">
                            <div class="card-body items-center text-center p-6">
                                <div class="w-16 h-16 bg-info/10 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="fas fa-poll text-info text-3xl"></i>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Survei SKM</h3>
                                <p class="text-sm opacity-70 mb-4">Bantu tingkatkan layanan</p>
                                <div class="badge badge-info">Isi Survei →</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer role="contentinfo" class="bg-gray-800 text-gray-200 dark:text-gray-300 py-10 px-4">
        <div class="max-w-screen-xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 bg-transparent">
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <?php if(config('app.logo')): ?>
                            <img src="<?php echo e(asset(config('app.logo'))); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                                 class="h-12 w-12 object-contain rounded-lg bg-white p-1">
                        <?php else: ?>
                            <img src="<?php echo e(asset('images/kemenag-logo.png')); ?>" alt="<?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?> Logo"
                                 class="h-12 w-12 object-contain rounded-lg bg-white p-1">
                        <?php endif; ?>
                        <div>
                            <h3 class="font-bold text-white text-base"><?php echo e(config('app.name_full', 'PTSP MTsN 2 KOTA MALANG')); ?></h3>
                            <p class="text-sm opacity-80">Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </div>
                    <p class="text-sm opacity-80">
                        Sistem pelayanan terpadu sesuai Permen PANRB 15/2014 untuk kemudahan akses layanan.
                    </p>
                    <div class="flex gap-4 mt-4">
                        <a href="#" class="text-2xl text-white" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-2xl text-white" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Kontak Kami</h6>
                    <div class="flex items-start gap-2 text-sm mb-2 text-white"><i class="fas fa-map-marker-alt mt-1 text-orange-500"></i><span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-phone text-orange-500"></i><span>(0341) 711500</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-envelope text-orange-500"></i><span>mtsnmalang2adm@gmail.com</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-globe text-orange-500"></i><span>www.mtsn2kotamalang.sch.id</span></div>
                    <div class="flex items-center gap-2 text-sm mb-2 text-white"><i class="fas fa-comment text-orange-500"></i><span>0851 8336 7500 (PTSP)</span></div>
                    <div class="flex items-center gap-2 text-sm text-white"><i class="fas fa-comment text-orange-500"></i><span>0851 8337 5008 (Pengaduan)</span></div>
                </nav>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Jam Operasional</h6>
                    <div class="text-sm">
                        <div class="flex items-start gap-2 mb-2 text-white">
                            <i class="fas fa-clock mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Senin - Kamis</strong><br>
                                <span class="opacity-80">07.00 - 15.00 WIB</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 mb-2 text-white">
                            <i class="fas fa-clock mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Jumat</strong><br>
                                <span class="opacity-80">07.00 - 11.00 WIB</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 text-white">
                            <i class="fas fa-calendar-times mt-1 text-orange-500"></i>
                            <div>
                                <strong class="text-white">Sabtu - Minggu</strong><br>
                                <span class="opacity-80">Tutup</span>
                            </div>
                        </div>
                    </div>
                </nav>

                <nav class="bg-transparent">
                    <h6 class="font-bold uppercase mb-4 text-white">Link Terkait</h6>
                    <a href="https://kemenag.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kementerian Agama RI</span></a>
                    <a href="https://kanwil.kemenag.go.id/jatim" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kanwil Kemenag Jatim</span></a>
                    <a href="https://kankemenag.malangkota.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>Kemenag Kota Malang</span></a>
                    <a href="https://lapor.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm mb-2 text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>SP4N Lapor</span></a>
                    <a href="https://sippn.menpan.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-sm text-white no-underline"><i class="fas fa-external-link-alt text-orange-500"></i><span>SIPPN Menpan</span></a>
                </nav>
            </div>
            <div class="mt-10 pt-10 border-t border-gray-700 dark:border-gray-600">
                <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left gap-4">
                    <p class="text-sm opacity-80">
                        <i class="fas fa-code-branch mr-2"></i>
                        <span class="mr-3">v1.0.0</span>
                        &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name_full', 'PTSP MTsN 2 Kota Malang')); ?>. Hak Cipta Dilindungi.
                    </p>
                    <p class="text-sm opacity-80">
                        <i class="fas fa-code mr-1"></i> Dikembangkan dengan <i class="fas fa-heart text-red-500 mx-1"></i> oleh Tim PUSKOM
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Buttons -->
    <div class="fixed bottom-6 right-6 flex flex-col gap-3 z-50">
        <a href="https://wa.me/628518367500" target="_blank" class="btn btn-circle btn-success shadow-xl">
            <i class="fab fa-whatsapp text-2xl"></i>
        </a>
        <button onclick="window.scrollTo({top:0,behavior:'smooth'})" class="btn btn-circle btn-primary shadow-xl" id="backToTop">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script>
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            backToTop.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
        });
        backToTop.style.display = 'none';

        // Theme toggle (if needed)
        function toggleTheme() {
            const html = document.documentElement;
            const theme = html.getAttribute('data-theme');
            html.setAttribute('data-theme', theme === 'dark' ? 'light' : 'dark');
            localStorage.setItem('theme', theme === 'dark' ? 'light' : 'dark');
        }

        // Load theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</body>
</html>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\welcome_daisyui_attempt.blade.php ENDPATH**/ ?>