@extends('layouts.public')

@section('title', $pengumuman->title . ' - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @vite(['resources/css/pengumuman.css'])
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
                        <div class="badge badge-primary">{{ $pengumuman->category ?? 'Umum' }}</div>
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
                            <span>{{ $pengumuman->author ?? 'Admin' }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span>
                                <i class="fas fa-eye mr-2"></i>
                                <span>Dilihat 0 kali</span>
                            </span>
                            
                            @if($pengumuman->attachment || $pengumuman->url)
                                <div class="flex gap-2">
                                    @if($pengumuman->attachment)
                                    <a href="{{ asset('storage/' . $pengumuman->attachment) }}" 
                                       target="_blank" 
                                       class="btn btn-outline btn-info btn-xs">
                                        <i class="fas fa-file-pdf mr-1"></i>PDF
                                    </a>
                                    @endif
                                    @if($pengumuman->url)
                                    <a href="{{ $pengumuman->url }}" 
                                       target="_blank" 
                                       class="btn btn-outline btn-success btn-xs">
                                        <i class="fas fa-external-link-alt mr-1"></i>URL
                                    </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="prose max-w-none mt-4">
                        {!! nl2br(e($pengumuman->content)) !!}
                    </div>
                </div>
            </article>

            <!-- Share Section -->
            <div class="card bg-base-200 shadow-xl mt-8" data-aos="fade-up">
                <div class="card-body items-center text-center">
                    <h2 class="card-title text-base-content/70">Bagikan Pengumuman Ini</h2>
                    <div class="flex justify-center gap-4 mt-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                           target="_blank"
                           class="btn btn-primary btn-sm">
                            <i class="fab fa-facebook-f mr-1"></i>
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($pengumuman->title) }}&url={{ urlencode(request()->url()) }}"
                           target="_blank"
                           class="btn btn-info btn-sm text-white">
                            <i class="fab fa-twitter mr-1"></i>
                            Twitter
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($pengumuman->title . ' - ' . request()->url()) }}"
                           target="_blank"
                           class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp mr-1"></i>
                            WhatsApp
                        </a>
                        <button class="btn btn-secondary btn-sm" onclick="copyToClipboard()">
                            <i class="fas fa-copy mr-1"></i>
                            Salin Tautan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            // Tampilkan notifikasi bahwa tautan telah disalin
            const originalText = document.querySelector('.btn-secondary.share-btn').innerHTML;
            document.querySelector('.btn-secondary.share-btn').innerHTML = '<i class="fas fa-check me-1"></i> Tersalin!';
            
            setTimeout(function() {
                document.querySelector('.btn-secondary.share-btn').innerHTML = originalText;
            }, 2000);
        });
    }
</script>
@endsection