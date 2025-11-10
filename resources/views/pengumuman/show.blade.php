@extends('layouts.public')

@section('title', $pengumuman->title . ' - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Announcement Detail Styles */
    .announcement-detail {
        border: 1px solid var(--bs-border-color) !important;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .announcement-detail:hover {
        transform: translateY(-2px);
        background-color: rgba(20, 83, 45, 0.03);
        border-color: var(--bs-primary) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
    }
    
    [data-theme="dark"] .announcement-detail:hover {
        background-color: rgba(20, 83, 45, 0.1);
        border-color: var(--bs-primary) !important;
    }
    
    .pengumuman-content {
        line-height: 1.8;
        font-size: 1.1em;
        color: var(--bs-text);
    }
    
    .pengumuman-content p {
        margin-bottom: 1.5rem;
    }
    
    .pengumuman-content h1,
    .pengumuman-content h2,
    .pengumuman-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        color: var(--bs-text);
    }
    
    .pengumuman-content ul,
    .pengumuman-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .pengumuman-content li {
        margin-bottom: 0.5rem;
    }
    
    [data-theme="dark"] .pengumuman-content {
        color: var(--bs-text);
    }
    
    [data-theme="dark"] .card {
        background: var(--bs-surface);
        border-color: var(--bs-border);
    }
    
    /* Share Section Styling */
    .share-section {
        background-color: var(--bs-surface);
        border-radius: 1rem;
        padding: 1.5rem;
    }
    
    .share-btn {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* Custom badge styling */
    .announcement-badge {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
    }
    
    .announcement-badge.bg-primary {
        background-color: rgba(20, 83, 45, 0.2) !important;
        color: var(--bs-primary) !important;
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
            <article class="card h-100 border-0 shadow-sm announcement-detail" style="background-color: var(--bs-bg);" data-aos="fade-up">
                <div class="card-body p-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                        <span class="announcement-badge bg-primary">
                            {{ $pengumuman->category ?? 'Umum' }}
                        </span>
                        <div class="text-end">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Diterbitkan: {{ $pengumuman->publish_date->format('d M Y') }}
                                @if($pengumuman->end_date)
                                    <br>
                                    <i class="fas fa-clock me-1"></i>
                                    Berakhir: {{ $pengumuman->end_date->format('d M Y') }}
                                @endif
                            </small>
                        </div>
                    </div>

                    <h1 class="fw-bold mb-4" style="color: var(--bs-text);">
                        {{ $pengumuman->title }}
                    </h1>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-user me-2"></i>
                                <span>{{ $pengumuman->author ?? 'Admin' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end text-muted gap-3">
                                <span>
                                    <i class="fas fa-eye me-2"></i>
                                    <span>Dilihat 0 kali</span>
                                </span>
                                
                                @if($pengumuman->attachment || $pengumuman->url)
                                    <div class="d-flex gap-2">
                                        @if($pengumuman->attachment)
                                        <a href="{{ asset('storage/' . $pengumuman->attachment) }}" 
                                           target="_blank" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-file-pdf me-1"></i>PDF
                                        </a>
                                        @endif
                                        @if($pengumuman->url)
                                        <a href="{{ $pengumuman->url }}" 
                                           target="_blank" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-external-link-alt me-1"></i>URL
                                        </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="pengumuman-content mt-4">
                        {!! nl2br(e($pengumuman->content)) !!}
                    </div>
                </div>
            </article>

            <!-- Share Section -->
            <div class="share-section mt-5" data-aos="fade-up">
                <h6 class="text-center text-muted mb-4">Bagikan Pengumuman Ini</h6>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                       target="_blank"
                       class="btn btn-primary btn-sm share-btn">
                        <i class="fab fa-facebook-f me-1"></i>
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($pengumuman->title) }}&url={{ urlencode(request()->url()) }}"
                       target="_blank"
                       class="btn btn-info btn-sm share-btn text-white">
                        <i class="fab fa-twitter me-1"></i>
                        Twitter
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($pengumuman->title . ' - ' . request()->url()) }}"
                       target="_blank"
                       class="btn btn-success btn-sm share-btn">
                        <i class="fab fa-whatsapp me-1"></i>
                        WhatsApp
                    </a>
                    <button class="btn btn-secondary btn-sm share-btn" onclick="copyToClipboard()">
                        <i class="fas fa-copy me-1"></i>
                        Salin Tautan
                    </button>
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