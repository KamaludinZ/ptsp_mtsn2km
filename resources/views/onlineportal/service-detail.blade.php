@extends('layouts.public')

@section('title', $service->name . ' - Standar Pelayanan')

@php
    // 14 komponen standar pelayanan (Permen PANRB 15/2014), shared with the request forms.
    $standard = \App\Support\ServiceStandard::items($service);
    $modeLabel = \App\Support\ServiceStandard::MODES[$service->mode] ?? 'Online & Offline';
    // Offline services are requested at the counter only (Service::scopeRequestable).
    $onlineChannel = in_array($service->mode, ['online', 'hybrid'], true);
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
                    <div class="lead rich-text mb-4" style="max-width: 60ch;">{{ \App\Support\RichText::render($service->description) }}</div>
                @endif

                <div class="d-flex flex-wrap align-items-center gap-3">
                    @if (! $service->is_active)
                        {{-- Only staff open an inactive service's page. --}}
                        <span class="fw-semibold" data-inactive-note><i class="fas fa-circle-pause me-2" aria-hidden="true"></i>Layanan ini sedang nonaktif: tidak tampil di katalog dan tidak menerima permohonan.</span>
                    @elseif ($visitor->canApply($service))
                        <a href="{{ route('onlineportal.service.apply', $service->slug) }}" class="btn btn-light btn-lg fw-semibold">
                            <i class="fas fa-file-circle-plus me-2" aria-hidden="true"></i>Ajukan Layanan Online
                        </a>
                        @guest
                            <span class="small text-white-50">Anda akan diminta masuk atau mendaftar terlebih dahulu.</span>
                        @endguest
                    @elseif ($onlineChannel)
                        {{-- Staff: the portal is not theirs, or not for this service. --}}
                        <span class="small" data-staff-note><i class="fas fa-id-badge me-2" aria-hidden="true"></i>Anda masuk sebagai petugas. Pemohon mengajukan layanan ini lewat portal; yang datang langsung didaftarkan di loket.</span>
                        @if ($visitor->counter)
                            <a href="{{ \App\Filament\Pages\FrontDesk\RegisterService::getUrl(panel: 'admin') }}" class="btn btn-light fw-semibold">
                                <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>Daftarkan di Loket
                            </a>
                        @endif
                    @else
                        <span class="fw-semibold"><i class="fas fa-store me-2" aria-hidden="true"></i>Diajukan langsung di loket PTSP pada jam layanan.</span>
                    @endif
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
                            {{ \App\Support\ServiceStandard::applicants($service) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($service->activeTemplates->isNotEmpty())
            @php
                $available = $service->activeTemplates->filter(fn ($template) => $template->fileInfo());
                $icon = fn ($template) => match (strtolower(pathinfo($template->file_name ?: $template->file_path, PATHINFO_EXTENSION))) {
                    'pdf' => 'fa-file-pdf text-danger',
                    'doc', 'docx' => 'fa-file-word text-primary',
                    'xls', 'xlsx' => 'fa-file-excel text-success',
                    default => 'fa-file text-secondary',
                };
            @endphp
            <!-- Template berkas: diunduh, dilengkapi, lalu diunggah saat mengajukan -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="templates-heading">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                        <div>
                            <h2 id="templates-heading" class="h5 fw-bold mb-1">
                                <i class="fas fa-file-download me-2" style="color: var(--bs-primary);" aria-hidden="true"></i>Template Berkas
                            </h2>
                            <p class="text-muted small mb-0">{{ $service->activeTemplates->count() }} template untuk layanan ini{{ $service->activeTemplates->where('is_required', true)->count() ? ', ' . $service->activeTemplates->where('is_required', true)->count() . ' wajib dilengkapi' : '' }}.</p>
                        </div>
                        @if ($available->count() > 1)
                            <a href="{{ route('onlineportal.service.templates.zip', $service->slug) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-archive me-1" aria-hidden="true"></i>Unduh semua (.zip)
                            </a>
                        @endif
                    </div>

                    <ol class="list-unstyled d-flex flex-wrap gap-3 small text-muted my-3" aria-label="Cara memakai template">
                        <li><span class="badge rounded-pill bg-primary me-1">1</span>Unduh template</li>
                        <li><span class="badge rounded-pill bg-primary me-1">2</span>Lengkapi dan tanda tangani bila perlu</li>
                        @if ($onlineChannel)
                            <li><span class="badge rounded-pill bg-primary me-1">3</span>Unggah saat <a href="{{ route('onlineportal.service.apply', $service->slug) }}">mengajukan permohonan</a></li>
                        @else
                            <li><span class="badge rounded-pill bg-primary me-1">3</span>Bawa ke loket PTSP saat mengajukan permohonan</li>
                        @endif
                    </ol>

                    <ul class="list-group list-group-flush">
                        @foreach ($service->activeTemplates as $template)
                            @php
                                $info = $template->fileInfo();
                            @endphp
                            <li class="list-group-item px-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <span class="d-flex align-items-start gap-2">
                                    <i class="fas {{ $icon($template) }} fa-lg mt-1" aria-hidden="true"></i>
                                    <span>
                                        <span class="fw-semibold">{{ $template->nama }}</span>
                                        @if ($template->is_required)
                                            <span class="badge bg-warning text-dark ms-1">Wajib</span>
                                        @endif
                                        @if ($template->petunjuk)
                                            <span class="d-block small">{{ $template->petunjuk }}</span>
                                        @endif
                                        <span class="d-block small text-muted">{{ $info ?? 'Berkas sedang tidak tersedia. Silakan hubungi petugas PTSP.' }}</span>
                                    </span>
                                </span>
                                @if ($info)
                                    <a href="{{ $template->downloadUrl() }}" class="btn btn-sm btn-outline-primary" aria-label="Unduh {{ $template->nama }}">
                                        <i class="fas fa-download me-1" aria-hidden="true"></i>Unduh
                                    </a>
                                @else
                                    <span class="badge bg-secondary">Tidak tersedia</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if ($service->activeTemplates->isEmpty())
            <p class="small text-muted mb-4">
                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>Layanan ini tidak memakai template berkas; cukup siapkan berkas persyaratan di bawah saat mengajukan.
            </p>
        @endif

        <!-- 14 komponen standar pelayanan -->
        <section aria-labelledby="standard-heading">
            <h2 id="standard-heading" class="h4 fw-bold mb-1">Standar Pelayanan</h2>
            <p class="text-muted mb-4">14 komponen standar pelayanan sesuai Permen PANRB Nomor 15 Tahun 2014.</p>

            <div class="row g-3">
                @foreach ($standard as $item)
                    @php
                        $content = $item['content'];
                        $list = $item['list'];
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
                                    <div class="rich-text mb-0">{{ \App\Support\RichText::render($content) }}</div>
                                @else
                                    <p class="text-muted fst-italic mb-0">Belum diisi.</p>
                                @endif

                                @if ($item['link'])
                                    <a href="{{ $item['link'][0] }}" class="d-inline-block mt-3 fw-semibold">
                                        {{ $item['link'][1] }} <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                                    </a>
                                @endif
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
