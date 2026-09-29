@extends('layouts.public')

@section('title', $service->name . ' - Standar Pelayanan')

@php
    // 14 komponen standar pelayanan (Permen PANRB 15/2014). Isian dari tabel
    // service_components diutamakan; beberapa komponen juga punya kolom sendiri
    // di tabel services.
    $components = $service->components->pluck('content', 'component_type');

    $requirementsText = $service->getAttributes()['requirements'] ?? null;
    $requirementsList = json_decode((string) $requirementsText, true);

    $standard = [
        ['key' => 'dasar_hukum', 'no' => 1, 'icon' => 'fa-scale-balanced', 'title' => 'Dasar Hukum'],
        ['key' => 'persyaratan', 'no' => 2, 'icon' => 'fa-list-check', 'title' => 'Persyaratan',
            'list' => is_array($requirementsList) ? $requirementsList : null, 'value' => $requirementsText],
        ['key' => 'mekanisme', 'no' => 3, 'icon' => 'fa-diagram-project', 'title' => 'Sistem, Mekanisme, dan Prosedur', 'value' => $service->mechanism],
        ['key' => 'jangka_waktu', 'no' => 4, 'icon' => 'fa-clock', 'title' => 'Jangka Waktu Penyelesaian', 'value' => $service->processing_time],
        ['key' => 'biaya', 'no' => 5, 'icon' => 'fa-money-bill-wave', 'title' => 'Biaya/Tarif',
            'value' => $service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis (Rp 0)'],
        ['key' => 'produk_layanan', 'no' => 6, 'icon' => 'fa-file-signature', 'title' => 'Produk Pelayanan', 'value' => $service->product],
        ['key' => 'sarana_prasarana', 'no' => 7, 'icon' => 'fa-building', 'title' => 'Sarana dan Prasarana'],
        ['key' => 'kompetensi_pelaksana', 'no' => 8, 'icon' => 'fa-user-tie', 'title' => 'Kompetensi Pelaksana'],
        ['key' => 'pengawasan_internal', 'no' => 9, 'icon' => 'fa-user-shield', 'title' => 'Pengawasan Internal'],
        ['key' => 'penanganan_pengaduan', 'no' => 10, 'icon' => 'fa-comments', 'title' => 'Penanganan Pengaduan, Saran, dan Masukan',
            'value' => $service->complaint_handling, 'link' => [route('supervision.complaint.submit'), 'Sampaikan pengaduan']],
        ['key' => 'jumlah_pelaksana', 'no' => 11, 'icon' => 'fa-users', 'title' => 'Jumlah Pelaksana'],
        ['key' => 'jaminan_pelayanan', 'no' => 12, 'icon' => 'fa-handshake', 'title' => 'Jaminan Pelayanan'],
        ['key' => 'jaminan_keamanan', 'no' => 13, 'icon' => 'fa-lock', 'title' => 'Jaminan Keamanan dan Keselamatan'],
        ['key' => 'evaluasi_kinerja', 'no' => 14, 'icon' => 'fa-chart-line', 'title' => 'Evaluasi Kinerja Pelaksana',
            'link' => [route('survey.form'), 'Isi Survei Kepuasan (SKM & SPAK)']],
    ];

    $userTypeLabels = [
        'guru' => 'Guru', 'pegawai' => 'Pegawai', 'siswa' => 'Siswa', 'walimurid' => 'Wali Murid',
        'alumni' => 'Alumni', 'instansi' => 'Instansi', 'umum' => 'Masyarakat Umum',
    ];
    $modeLabel = ['online' => 'Online', 'offline' => 'Offline (Loket PTSP)'][$service->mode] ?? 'Online & Offline';
@endphp

@section('content')
<div class="py-5" style="background-color: var(--bs-gray-50);">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('onlineportal.service.catalog') }}">Katalog Layanan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <section class="card border-0 shadow-sm mb-4 overflow-hidden" aria-labelledby="service-title">
            <div class="card-body p-4 p-lg-5 text-white" style="background: var(--gradient-primary);">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-white text-dark px-3 py-2"><i class="fas fa-hashtag me-1" aria-hidden="true"></i>{{ $service->code }}</span>
                    <span class="badge bg-light text-dark px-3 py-2"><i class="fas fa-route me-1" aria-hidden="true"></i>{{ $modeLabel }}</span>
                    @if ($service->is_digital_product)
                        <span class="badge bg-light text-dark px-3 py-2"><i class="fas fa-file-pdf me-1" aria-hidden="true"></i>Produk digital</span>
                    @endif
                    @foreach ($service->categories as $category)
                        <span class="badge bg-light text-dark px-3 py-2"><i class="fas fa-tag me-1" aria-hidden="true"></i>{{ $category->name }}</span>
                    @endforeach
                </div>
                <h1 id="service-title" class="h2 fw-bold mb-3">{{ $service->name }}</h1>
                @if ($service->description)
                    <p class="lead mb-4" style="max-width: 60ch;">{{ $service->description }}</p>
                @endif

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="{{ route('onlineportal.service.apply', $service->slug) }}" class="btn btn-light btn-lg fw-semibold">
                        <i class="fas fa-file-circle-plus me-2" aria-hidden="true"></i>Ajukan Layanan Online
                    </a>
                    @guest
                        <span class="small text-white-50">Anda akan diminta masuk atau mendaftar terlebih dahulu.</span>
                    @endguest
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 text-center text-md-start">
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Waktu penyelesaian</div>
                        <div class="fw-bold">{{ $service->processing_time ?: 'Belum diisi' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Biaya</div>
                        <div class="fw-bold">{{ $service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis' }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="small text-muted">Dapat diajukan oleh</div>
                        <div class="fw-bold">
                            {{ collect((array) $service->user_types_allowed)->map(fn ($type) => $userTypeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 14 komponen standar pelayanan -->
        <section aria-labelledby="standard-heading">
            <h2 id="standard-heading" class="h4 fw-bold mb-1">Standar Pelayanan</h2>
            <p class="text-muted mb-4">14 komponen standar pelayanan sesuai Permen PANRB Nomor 15 Tahun 2014.</p>

            <div class="row g-3">
                @foreach ($standard as $item)
                    @php
                        $content = $components[$item['key']] ?? ($item['value'] ?? null);
                        $list = $components->has($item['key']) ? null : ($item['list'] ?? null);
                    @endphp
                    <div class="col-md-6">
                        <article class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="h6 fw-bold d-flex align-items-center gap-2 mb-3">
                                    <span class="badge rounded-pill" style="background: var(--bs-primary);">{{ $item['no'] }}</span>
                                    <i class="fas {{ $item['icon'] }}" style="color: var(--bs-primary);" aria-hidden="true"></i>
                                    {{ $item['title'] }}
                                </h3>

                                @if ($list)
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($list as $entry)
                                            <li class="mb-2"><i class="fas fa-check text-success me-2" aria-hidden="true"></i>{{ $entry }}</li>
                                        @endforeach
                                    </ul>
                                @elseif (filled($content))
                                    <p class="mb-0" style="white-space: pre-line;">{{ $content }}</p>
                                @else
                                    <p class="text-muted fst-italic mb-0">Belum diisi.</p>
                                @endif

                                @isset($item['link'])
                                    <a href="{{ $item['link'][0] }}" class="d-inline-block mt-3 fw-semibold">
                                        {{ $item['link'][1] }} <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                                    </a>
                                @endisset
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="text-center mt-5">
            <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Kembali ke Katalog Layanan
            </a>
        </div>
    </div>
</div>
@endsection
