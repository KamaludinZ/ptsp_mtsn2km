@props(['service', 'visitor', 'columns' => false, 'excerpt' => null])

{{--
    Detail layanan inside a card of /services (grid and list view): the six
    main components, sanitised rich text (App\Support\RichText), and how to
    apply. The 14 components are on the service's own page. $columns puts
    the components side by side (list view). How to apply follows
    $visitor (App\Support\ServiceVisitor): a visitor not signed in logs in
    through the apply address itself, so the login returns to the form;
    staff who cannot apply for it are pointed to the counter instead. The
    card shows the description cut to $excerpt characters; when it is cut,
    the detail opens with the whole of it.
--}}
@php($requirements = filled($service->requirements) ? json_decode($service->requirements, true) : null)

<div {{ $attributes->class(['service-detail']) }}>
    <a href="{{ route('onlineportal.service.detail', $service->slug) }}" class="d-inline-block mb-3 fw-semibold">Lihat 14 komponen standar pelayanan <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></a>

    @if ($excerpt && mb_strwidth(\App\Support\RichText::plain($service->description)) > $excerpt)
        <div class="mb-4">
            <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-circle-info me-2" aria-hidden="true"></i> Tentang Layanan</h6>
            <div class="text-muted rich-text">{{ \App\Support\RichText::render($service->description) }}</div>
        </div>
    @endif

    <div @class(['row' => $columns])>
        <div @class(['col-md-6' => $columns])>
            <div class="mb-4">
                <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-list-check me-2" aria-hidden="true"></i> Persyaratan</h6>
                @if (is_array($requirements))
                    {{-- Older value: a JSON list --}}
                    <ul class="list-unstyled">
                        @foreach ($requirements as $requirement)
                            <li class="mb-2"><i class="fas fa-check text-success me-2" aria-hidden="true"></i> {{ $requirement }}</li>
                        @endforeach
                    </ul>
                @elseif (filled($service->requirements))
                    <div class="text-muted rich-text">{{ \App\Support\RichText::render($service->requirements) }}</div>
                @else
                    <p class="text-muted">-</p>
                @endif
            </div>

            <div class="mb-4">
                <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-cogs me-2" aria-hidden="true"></i> Sistem & Prosedur</h6>
                <div class="text-muted rich-text">{{ filled($service->mechanism) ? \App\Support\RichText::render($service->mechanism) : '-' }}</div>
            </div>
        </div>

        <div @class(['col-md-6' => $columns])>
            @if ($columns)
                <div class="mb-4">
                    <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-money-bill-wave me-2" aria-hidden="true"></i> Informasi Biaya</h6>
                    <p class="text-muted mb-0">{{ $service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis' }}</p>
                </div>
            @endif

            <div class="mb-4">
                <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-file-alt me-2" aria-hidden="true"></i> Produk Layanan</h6>
                <div class="text-muted rich-text">{{ filled($service->product) ? \App\Support\RichText::render($service->product) : '-' }}</div>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold mb-3" style="color: var(--bs-primary);"><i class="fas fa-headset me-2" aria-hidden="true"></i> Pengaduan</h6>
                <div class="text-muted rich-text mb-2">{{ filled($service->complaint_handling) ? \App\Support\RichText::render($service->complaint_handling) : 'Hubungi kami melalui halaman pengaduan' }}</div>
                <a href="{{ route('supervision.complaints.dashboard') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-comment-dots me-1" aria-hidden="true"></i> Ajukan Pengaduan
                </a>
            </div>

            @if (in_array($service->mode, ['online', 'hybrid'], true))
                <hr class="my-4">
                @if ($service->mode === 'hybrid' && $service->external_link)
                    <a href="{{ $service->external_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-external-link-alt me-2" aria-hidden="true"></i> Akses Layanan Eksternal
                    </a>
                @endif
                @if (! $visitor->user)
                    @if ($service->mode === 'online')
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                            <strong>Login diperlukan</strong> untuk mengajukan layanan online.
                            <div class="mt-2">
                                <a href="{{ route('onlineportal.service.apply', $service->slug) }}" class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-sign-in-alt me-1" aria-hidden="true"></i> Login
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user-plus me-1" aria-hidden="true"></i> Daftar
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('onlineportal.service.apply', $service->slug) }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i> Login untuk Pengajuan Internal
                        </a>
                    @endif
                @elseif ($visitor->canApply($service))
                    <a href="{{ route('onlineportal.service.apply', $service->slug) }}" @class(['btn w-100', 'btn-primary' => $service->mode === 'online', 'btn-outline-primary' => $service->mode === 'hybrid'])>
                        <i class="fas fa-paper-plane me-2" aria-hidden="true"></i> {{ $service->mode === 'online' ? 'Ajukan Permohonan' : 'Ajukan Permohonan Internal' }}
                    </a>
                @else
                    {{-- Staff: the portal is not theirs, or not for this service. --}}
                    <div class="alert alert-light border mb-0 small" data-staff-note>
                        <i class="fas fa-id-badge me-2" aria-hidden="true"></i>
                        Anda masuk sebagai petugas. Pemohon mengajukan layanan ini lewat portal; yang datang langsung didaftarkan di loket.
                        @if ($visitor->counter)
                            <div class="mt-2">
                                <a href="{{ \App\Filament\Pages\FrontDesk\RegisterService::getUrl(panel: 'admin') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-clipboard-list me-1" aria-hidden="true"></i> Daftarkan di Loket
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <hr class="my-4">
                <p class="text-muted mb-0"><i class="fas fa-store me-2" aria-hidden="true"></i> Layanan ini diajukan langsung di loket PTSP pada jam layanan.</p>
            @endif
        </div>
    </div>
</div>
