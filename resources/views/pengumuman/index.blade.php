@extends('layouts.public')

@section('title', 'Arsip Pengumuman - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Announcement List Item Styling */
    .announcement-list-item {
        border: 1px solid var(--bs-border-color) !important;
        border-left: 4px solid var(--bs-primary) !important;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .announcement-list-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        background-color: rgba(20, 83, 45, 0.03);
        border-color: var(--bs-primary) !important;
    }
    
    [data-theme="dark"] .announcement-list-item:hover {
        background-color: rgba(20, 83, 45, 0.1);
        border-color: var(--bs-primary) !important;
    }

    /* Dark mode specific fixes */
    [data-theme="dark"] .text-muted {
        color: #d1d5db !important;
    }

    [data-theme="dark"] .lead {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] p {
        color: var(--bs-text);
    }

    /* Announcement text always white on green gradient */
    .announcement-text {
        color: white !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
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
    
    .announcement-badge.bg-success {
        background-color: rgba(34, 197, 94, 0.2) !important;
        color: #22c55e !important;
    }
    
    .announcement-badge.bg-info {
        background-color: rgba(59, 130, 246, 0.2) !important;
        color: #3b82f6 !important;
    }
    
    .announcement-badge.bg-warning {
        background-color: rgba(245, 158, 11, 0.2) !important;
        color: #f59e0b !important;
    }
    
    /* Filter and search styling */
    .filter-section {
        background-color: var(--bs-surface);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .search-form .input-group-text {
        background-color: var(--bs-primary);
        color: white;
        border: 1px solid var(--bs-primary);
    }
    
    .filter-btn {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .result-count {
        font-size: 0.9rem;
        color: var(--bs-text);
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Informasi <span style="color: var(--bs-primary);">Pengumuman</span>
        </h1>
        <p class="lead text-muted">
            Arsip pengumuman dan informasi penting dari MTsN 2 Kota Malang
        </p>
    </div>

    <!-- Filter and Search Section -->
    <div class="filter-section" data-aos="fade-up">
        <form method="GET" action="{{ route('pengumuman.index') }}" class="search-form">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Cari pengumuman..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <select class="form-select" name="category">
                        <option value="">Semua Kategori</option>
                        <option value="akademik" {{ request('category') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                        <option value="administrasi" {{ request('category') == 'administrasi' ? 'selected' : '' }}>Administrasi</option>
                        <option value="kegiatan" {{ request('category') == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="lainnya" {{ request('category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                
                <div class="col-lg-2">
                    <input type="date" 
                           class="form-control" 
                           name="start_date" 
                           value="{{ request('start_date') }}"
                           placeholder="Tanggal Awal">
                </div>
                
                <div class="col-lg-2">
                    <input type="date" 
                           class="form-control" 
                           name="end_date" 
                           value="{{ request('end_date') }}"
                           placeholder="Tanggal Akhir">
                </div>
                
                <div class="col-lg-1">
                    <button type="submit" class="btn btn-primary w-100 filter-btn">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            
            <!-- Reset Filter Button -->
            @if(request()->query())
            <div class="mt-3">
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-redo me-1"></i>
                    Reset Filter
                </a>
            </div>
            @endif
            
            <!-- Active Filters -->
            @if(request()->query())
            <div class="mt-3">
                <small class="text-muted">Filter aktif:</small>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @if(request('search'))
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        Cari: {{ request('search') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['search' => ''])) }}" class="text-decoration-none ms-1">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    @endif
                    
                    @if(request('category'))
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        Kategori: {{ request('category') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['category' => ''])) }}" class="text-decoration-none ms-1">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    @endif
                    
                    @if(request('start_date'))
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        Tgl Awal: {{ request('start_date') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['start_date' => ''])) }}" class="text-decoration-none ms-1">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    @endif
                    
                    @if(request('end_date'))
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        Tgl Akhir: {{ request('end_date') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['end_date' => ''])) }}" class="text-decoration-none ms-1">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </form>
    </div>

    <!-- Announcements Section -->
    <section class="py-3" aria-labelledby="announcements-heading">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="result-count">
                @if($pengumumen->total() > 0)
                    Menampilkan {{ $pengumumen->firstItem() }} - {{ $pengumumen->lastItem() }} dari {{ $pengumumen->total() }} pengumuman
                @else
                    Tidak ditemukan pengumuman
                @endif
            </div>
        </div>

        @if($pengumumen->count() > 0)
            <div class="list-group">
                @foreach($pengumumen as $pengumuman)
                    <div class="list-group-item border-0 announcement-list-item" style="background-color: var(--bs-bg);">
                        <div class="row align-items-center">
                            <div class="col-lg-1">
                                <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-bullhorn text-white fs-4"></i>
                                </div>
                            </div>
                            
                            <div class="col-lg-7">
                                <h3 class="h5 fw-bold mb-1">
                                    <a href="{{ route('pengumuman.show', $pengumuman->id) }}" class="text-decoration-none text-dark">
                                        {{ $pengumuman->title }}
                                    </a>
                                </h3>
                                <p class="text-muted mb-0 small">
                                    {{ Str::limit(strip_tags($pengumuman->content), 150) }}
                                </p>
                            </div>
                            
                            <div class="col-lg-2">
                                <span class="announcement-badge bg-primary">
                                    {{ $pengumuman->category ?? 'Umum' }}
                                </span>
                            </div>
                            
                            <div class="col-lg-2 text-lg-end">
                                <div class="mb-1">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $pengumuman->publish_date->format('d M Y') }}
                                    </small>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $pengumuman->author ?? 'Admin' }}
                                    </small>
                                </div>

                                <a href="{{ route('pengumuman.show', $pengumuman->id) }}" class="btn btn-primary btn-sm mt-2">
                                    Baca
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-5 d-flex justify-content-center">
                {{ $pengumuman->appends(request()->query())->links('vendor.pagination.custom_pagination') }}
            </div>
        @else
            <div class="text-center py-5" data-aos="fade-up">
                <div class="mx-auto mb-4" style="width: 100px; height: 100px; background: rgba(20, 83, 45, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bullhorn fs-1" style="color: var(--bs-primary);"></i>
                </div>
                <h2 class="h3 fw-bold mb-3">
                    Tidak Ada <span style="color: var(--bs-primary);">Pengumuman</span>
                </h2>
                <p class="lead text-muted mb-4">
                    @if(request()->query())
                        Tidak ditemukan pengumuman dengan filter yang diterapkan
                    @else
                        Pengumuman akan ditampilkan di sini ketika tersedia
                    @endif
                </p>
                @if(request()->query())
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-redo me-2"></i>
                    Hapus Filter
                </a>
                @endif
            </div>
        @endif
    </section>

    <!-- Call to Action -->
    <section class="py-5 mt-5 text-center" data-aos="fade-up">
        <div class="p-5 rounded-4" style="background: var(--gradient-primary);">
            <h2 class="h2 fw-bold text-white mb-3">
                Ingin Mengetahui Lebih Banyak?
            </h2>
            <p class="lead text-white mb-4" style="opacity: 0.9;">
                Jelajahi layanan kami atau lacak tiket yang telah Anda ajukan
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('public.about') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-info-circle me-2"></i>
                    Tentang Kami
                </a>
                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-concierge-bell me-2"></i>
                    Layanan Kami
                </a>
            </div>
        </div>
    </section>
</div>
@endsection