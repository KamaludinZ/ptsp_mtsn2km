@extends('layouts.public')

@section('title', 'Tentang Kami - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @vite(['resources/css/about.css'])
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <h2 id="about-heading" class="text-3xl font-bold mb-4">
                    Apa itu <span class="text-primary">PTSP</span>?
                </h2>
                <p class="text-lg mb-4">
                    Pelayanan Terpadu Satu Pintu (PTSP) MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat.
                </p>
                <p class="mb-4">
                    Sistem ini dibangun sesuai dengan <strong class="text-primary">Permen PANRB 15/2014</strong> tentang Pedoman Standar Pelayanan yang menjamin transparansi, akuntabilitas, dan kecepatan pelayanan.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-double text-primary"></i>
                        </div>
                        <div>
                            <h5 class="font-bold mb-2">Transparan & Akuntabel</h5>
                            <p class="text-base-content/70">Setiap proses layanan dapat dilacak secara real-time dengan sistem monitoring terintegrasi</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="font-bold mb-2">Cepat & Tepat Waktu</h5>
                            <p class="text-base-content/70">Jaminan penyelesaian sesuai standar waktu layanan dengan SLA yang jelas</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-success"></i>
                        </div>
                        <div>
                            <h5 class="font-bold mb-2">Mudah Diakses</h5>
                            <p class="text-base-content/70">Layanan online 24/7 dan offline di loket PTSP dengan antarmuka yang user-friendly</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 lg:mt-0" data-aos="fade-left">
                <div class="p-8 rounded-2xl bg-primary text-primary-content">
                    <h3 class="text-2xl font-bold mb-4 text-center">14 Komponen Standar Pelayanan</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-gavel text-4xl mb-2 text-warning"></i>
                            <p class="font-semibold text-sm">Dasar Hukum</p>
                        </div>
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-list-alt text-4xl mb-2 text-info"></i>
                            <p class="font-semibold text-sm">Persyaratan</p>
                        </div>
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-sitemap text-4xl mb-2 text-success"></i>
                            <p class="font-semibold text-sm">Prosedur</p>
                        </div>
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-clock text-4xl mb-2 text-primary-focus"></i>
                            <p class="font-semibold text-sm">Jangka Waktu</p>
                        </div>
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-money-bill-wave text-4xl mb-2 text-warning"></i>
                            <p class="font-semibold text-sm">Biaya/Tarif</p>
                        </div>
                        <div class="bg-base-100/20 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 hover:scale-105">
                            <i class="fas fa-file-contract text-4xl mb-2 text-error"></i>
                            <p class="font-semibold text-sm">Produk Layanan</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="mb-0">+ 8 Komponen Lainnya</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Performance Stats Section -->
    <section class="py-16 bg-base-200 rounded-2xl" aria-labelledby="stats-heading">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12" data-aos="fade-up">
                <div class="badge badge-primary badge-lg mb-3">
                    <i class="fas fa-chart-line mr-2"></i>
                    Statistik Kinerja
                </div>
                <h2 id="stats-heading" class="text-3xl font-bold mb-3">
                    Kinerja <span class="text-primary">Pelayanan</span> Kami
                </h2>
                <p class="text-lg text-base-content/70">
                    Transparansi dan Akuntabilitas dalam Setiap Layanan
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="card bg-base-100 shadow-xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body items-center text-center">
                        <div class="radial-progress text-primary" style="--value:98; --size:12rem; --thickness: 0.5rem;" role="progressbar">98%</div>
                        <h3 class="card-title mt-4">Tingkat Kepuasan</h3>
                        <p class="text-sm text-base-content/70"><i class="fas fa-chart-line mr-2"></i>Berdasarkan SKM 2025</p>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body items-center text-center">
                        <div class="radial-progress text-secondary" style="--value:85; --size:12rem; --thickness: 0.5rem;" role="progressbar">2 Hari</div>
                        <h3 class="card-title mt-4">Rata-rata Waktu</h3>
                        <p class="text-sm text-base-content/70"><i class="fas fa-hourglass-half mr-2"></i>Penyelesaian Layanan</p>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-body items-center text-center">
                        <div class="radial-progress text-success" style="--value:100; --size:12rem; --thickness: 0.5rem;" role="progressbar">15+</div>
                        <h3 class="card-title mt-4">Jenis Layanan</h3>
                        <p class="text-sm text-base-content/70"><i class="fas fa-clipboard-check mr-2"></i>Sesuai Standar</p>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-body items-center text-center">
                        <div class="radial-progress text-info" style="--value:100; --size:12rem; --thickness: 0.5rem;" role="progressbar">24/7</div>
                        <h3 class="card-title mt-4">Akses Online</h3>
                        <p class="text-sm text-base-content/70"><i class="fas fa-wifi mr-2"></i>Kapan Saja, Dimana Saja</p>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Bar -->
            <div class="mt-12 p-8 rounded-2xl border-2 border-dashed border-primary bg-base-100" data-aos="fade-up" data-aos-delay="500">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-primary">5000+</div>
                        <div class="text-base-content/70">Layanan Diproses</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-secondary">100%</div>
                        <div class="text-base-content/70">Digitalisasi</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-success">4.8/5</div>
                        <div class="text-base-content/70">Rating Layanan</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-info">99.9%</div>
                        <div class="text-base-content/70">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Maklumat Pelayanan Section -->
    <section class="py-16" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="card lg:card-side bg-primary text-primary-content shadow-xl">
                <figure class="lg:w-1/2"><img src="{{ asset('images/MAKLUMAT-PELAYANAN-MTSN-2-KOTA-MALANG-31-Januari-2024-768x543.jpg') }}" alt="Maklumat Pelayanan" class="w-full h-full object-cover"></figure>
                <div class="card-body lg:w-1/2">
                    <h2 class="card-title text-3xl font-bold">Maklumat Pelayanan</h2>
                    <p>Kami menyediakan informasi layanan yang transparan dan akuntabel sesuai dengan standar pelayanan yang berlaku untuk memastikan kepuasan pengguna layanan.</p>
                    <div class="card-actions justify-end">
                        <a href="#" class="btn btn-neutral">
                            <i class="fas fa-info-circle mr-2"></i>
                            Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Reward and Punishment Section -->
    <section class="py-16" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="card lg:card-side bg-secondary text-secondary-content shadow-xl">
                <div class="card-body lg:w-1/2">
                    <h2 class="card-title text-3xl font-bold">Program Reward and Punishment</h2>
                    <p>Program penghargaan dan sanksi untuk mendorong kinerja pelayanan yang unggul dan akuntabel sesuai dengan standar pelayanan yang ditetapkan.</p>
                    <div class="card-actions justify-end">
                        <a href="#" class="btn btn-neutral">
                            <i class="fas fa-trophy mr-2"></i>
                            Selengkapnya
                        </a>
                    </div>
                </div>
                <figure class="lg:w-1/2"><img src="{{ asset('images/KOMPENSASI-PELAYANAN-MTSN-2-KOTA-MALANG_11zon-1-768x545.jpg') }}" alt="Program Reward and Punishment" class="w-full h-full object-cover"></figure>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 text-center" data-aos="fade-up">
        <div class="card bg-primary text-primary-content shadow-xl">
            <div class="card-body items-center text-center">
                <h2 class="card-title text-3xl font-bold">Siap Menggunakan Layanan Kami?</h2>
                <p class="text-lg">Akses layanan online 24/7 atau kunjungi loket PTSP kami</p>
                <div class="card-actions justify-center mt-4">
                    <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-neutral">
                        <i class="fas fa-list mr-2"></i>
                        Lihat Layanan
                    </a>
                    <a href="{{ route('onlineportal.track.ticket.form') }}" class="btn btn-ghost">
                        <i class="fas fa-search mr-2"></i>
                        Lacak Tiket
                    </a>
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
