@extends('layouts.public')

@section('title', 'Tentang Kami - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
        @vite(['resources/css/about.css'])
    @else
        {!! App\Helpers\AssetHelper::css('resources/css/about.css') !!}
    @endif
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
                        <div class="p-4 rounded-4" style="background: var(--gradient-primary); color: white;">
                            <h3 class="h2 fw-bold mb-4 text-center">14 Komponen Standar Pelayanan</h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-gavel fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Dasar Hukum</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-list-alt fs-3 mb-2 text-info icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Persyaratan</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-sitemap fs-3 mb-2 text-success icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Prosedur</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-clock fs-3 mb-2 text-primary icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Jangka Waktu</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-money-bill-wave fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Biaya/Tarif</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-3 p-3 text-center service-component hover-scale transition-all">
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
            <div class="stats-strip mt-5 p-4 rounded-3" data-aos="fade-up" data-aos-delay="500">
                <div class="row text-center">
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-boldNone stats-strip__value stats-strip__value--green">5000+</div>
                        <div class="text-muted small">Layanan Diproses</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-boldNone stats-strip__value stats-strip__value--orange">100%</div>
                        <div class="text-muted small">Digitalisasi</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-boldNone stats-strip__value stats-strip__value--emerald">4.8/5</div>
                        <div class="text-muted small">Rating Layanan</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="display-6 fw-boldNone stats-strip__value stats-strip__value--blue">99.9%</div>
                        <div class="text-muted small">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Maklumat Pelayanan Section -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="row g-0">
                    <div class="col-lg-6">
                        <img src="{{ asset('images/MAKLUMAT-PELAYANAN-MTSN-2-KOTA-MALANG-31-Januari-2024-768x543.jpg') }}" alt="Maklumat Pelayanan" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="col-lg-6">
                        <div class="card-body p-5 d-flex flex-column justify-content-center h-100" style="background: var(--gradient-primary); color: white;">
                            <h2 class="h2 fw-bold mb-4">Maklumat Pelayanan</h2>
                            <p class="lead mb-4">Kami menyediakan informasi layanan yang transparan dan akuntabel sesuai dengan standar pelayanan yang berlaku untuk memastikan kepuasan pengguna layanan.</p>
                            <div class="mt-auto">
                                <a href="#" class="btn btn-light btn-lg">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Reward and Punishment Section -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="row g-0">
                    <div class="col-lg-6 order-lg-2">
                        <img src="{{ asset('images/KOMPENSASI-PELAYANAN-MTSN-2-KOTA-MALANG_11zon-1-768x545.jpg') }}" alt="Program Reward and Punishment" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <div class="card-body p-5 d-flex flex-column justify-content-center h-100" style="background: var(--gradient-secondary); color: white;">
                            <h2 class="h2 fw-bold mb-4">Program Reward and Punishment</h2>
                            <p class="lead mb-4">Program penghargaan dan sanksi untuk mendorong kinerja pelayanan yang unggul dan akuntabel sesuai dengan standar pelayanan yang ditetapkan.</p>
                            <div class="mt-auto">
                                <a href="#" class="btn btn-light btn-lg">
                                    <i class="fas fa-trophy me-2"></i>
                                    Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions (FAQ)</h2>
                <p class="lead text-muted">Temukan jawaban atas pertanyaan yang sering diajukan.</p>
            </div>

            @if($faqs->count())
            <div class="accordion" id="faqAccordion">
                @foreach($faqs as $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $faq->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $faq->id }}" aria-expanded="false" aria-controls="collapse{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="collapse{{ $faq->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $faq->id }}" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            {!! $faq->safeAnswer() !!}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center">
                <p>Saat ini belum ada FAQ yang tersedia.</p>
            </div>
            @endif
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5" data-aos="fade-up">
        <div class="container">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-body text-center p-5" style="background: var(--gradient-primary); color: white;">
                    <h2 class="display-5 fw-bold mb-3">Siap Menggunakan Layanan Kami?</h2>
                    <p class="lead mb-4">Akses layanan online 24/7 atau kunjungi loket PTSP kami</p>
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
