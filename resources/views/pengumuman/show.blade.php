@extends('layouts.public')

@section('title', $pengumuman->title . ' - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
        @vite(['resources/css/pengumuman.css'])
    @else
        {!! App\Helpers\AssetHelper::css('resources/css/pengumuman.css') !!}
    @endif
    <style>
        /* Green badge for category */
        .badge-category-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white;
            border: none;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        /* Dark mode support */
        [data-theme="dark"] .badge-category-green {
            background: #059669 !important;
            color: white !important;
        }
    </style>
@endpush

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Detail <span style="color: var(--bs-primary);">Pengumuman</span>
        </h1>
        <p class="lead text-muted">
            Informasi lengkap tentang pengumuman terpilih
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Back Button -->
            <div class="mb-4" data-aos="fade-up">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali ke Arsip Pengumuman
                </a>
            </div>

            <!-- Announcement Detail -->
            <article class="card bg-base-100 shadow-xl" data-aos="fade-up">
                <div class="card-body p-8">
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="badge badge-category-green">{{ strtoupper($pengumuman->category ?? 'Umum') }}</div>
                        <div class="text-sm text-base-content/70">
                            <i class="fas fa-calendar mr-1"></i>
                            Diterbitkan: {{ $pengumuman->publish_date->format('d M Y') }}
                            @if($pengumuman->end_date)
                                <br>
                                <i class="fas fa-clock mr-1"></i>
                                Berakhir: {{ $pengumuman->end_date->format('d M Y') }}
                            @endif
                        </div>
                    </div>

                    <h1 class="text-3xl font-bold mb-4">
                        {{ $pengumuman->title }}
                    </h1>

                    <div class="flex flex-wrap justify-between items-center mb-4 text-sm text-base-content/70">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-2"></i>
                            <span>{{ $pengumuman->authorName() }}</span>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="flex items-center gap-2">
                                @if($pengumuman->attachment)
                                <a href="{{ asset('storage/' . $pengumuman->attachment) }}"
                                   target="_blank"
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-file-pdf mr-1"></i>PDF
                                </a>
                                @endif
                                @if($pengumuman->url)
                                <a href="{{ $pengumuman->url }}"
                                   target="_blank"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i>URL
                                </a>
                                @endif
                                <span>
                                    <i class="fas fa-eye mr-2"></i>
                                    <span>Dilihat {{ $pengumuman->view_count }} kali</span>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="prose rich-text max-w-none mt-4">
                        {{ \App\Support\RichText::render($pengumuman->content) }}
                    </div>
                </div>
            </article>

        </div>
    </div>
</div>
@endsection