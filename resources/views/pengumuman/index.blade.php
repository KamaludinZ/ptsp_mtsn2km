@extends('layouts.public')

@section('title', 'Arsip Pengumuman - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Announcement Card Modern Style */
    .announcement-card-modern {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(20, 83, 45, 0.1) !important;
    }

    .announcement-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid rgba(20, 83, 45, 0.3) !important;
    }

    /* Page Header Enhancement */
    .page-header-enhanced {
        background: linear-gradient(135deg, rgba(20, 83, 45, 0.03) 0%, rgba(234, 88, 12, 0.03) 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    /* Announcement title styling */
    .announcement-title {
        color: #000000 !important;
        font-weight: 600;
    }

    /* Green badge for category */
    .badge-category-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        min-height: 2em;
    }

    /* Dark mode support for filter card */
    [data-theme="dark"] .filter-card-enhanced {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .search-input-enhanced {
        background-color: #1f2937 !important;
        border-color: #4b5563 !important;
        color: white !important;
    }

    [data-theme="dark"] .badge-category-green {
        background: #059669 !important;
        color: white !important;
    }

    [data-theme="dark"] .form-label {
        color: white !important;
    }

    [data-theme="dark"] .announcement-title {
        color: #f9fafb !important;
    }

    /* Dark mode support */
    [data-theme="dark"] .page-header-enhanced {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .announcement-card-modern {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #f9fafb !important;
    }

    [data-theme="dark"] .announcement-card-modern .card-body {
        color: #f9fafb !important;
    }

    [data-theme="dark"] .text-base-content\/70 {
        color: #d1d5db !important;
    }

    [data-theme="dark"] .btn-primary {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <!-- Enhanced Page Header -->
    <div class="page-header-enhanced text-center mb-5" data-aos="fade-up">
        <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3 section-eyebrow"
             style="background-color: rgba(20, 83, 45, 0.1);">
            <i class="fas fa-bullhorn me-2" style="color: var(--bs-primary);"></i>
            <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Pengumuman</span>
        </div>
        <h1 class="display-4 fw-bold mb-3">
            Informasi <span style="color: var(--bs-primary);">Pengumuman</span>
        </h1>
        <p class="lead text-muted">
            Arsip pengumuman dan informasi penting dari
            <span class="fw-semibold" style="color: var(--bs-primary);">MTsN 2 Kota Malang</span>
        </p>
    </div>

    <!-- Filter and Search Section - maintaining original functionality with enhanced UI -->
    @php
        $categoryOptions = $categories ?? [];
        $activeFilters = array_filter([
            'search' => request('search') ? 'Cari: ' . request('search') : null,
            'category' => request('category') ? 'Kategori: ' . ($categoryOptions[request('category')] ?? request('category')) : null,
            'start_date' => request('start_date') ? 'Dari: ' . request('start_date') : null,
            'end_date' => request('end_date') ? 'Sampai: ' . request('end_date') : null,
        ]);
    @endphp
    <div class="ann-filter" data-aos="fade-up">
        <form method="GET" action="{{ route('pengumuman.index') }}" class="ann-filter__form" role="search" aria-label="Filter pengumuman">
            <div class="ann-filter__field ann-filter__field--search">
                <label for="ann-search">Cari pengumuman</label>
                <div class="ann-filter__control">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input id="ann-search" type="search" name="search" value="{{ request('search') }}" placeholder="Judul, isi, atau penulis..." autocomplete="off">
                </div>
            </div>
            <div class="ann-filter__field">
                <label for="ann-category">Kategori</label>
                <div class="ann-filter__control">
                    <select id="ann-category" name="category">
                        <option value="">Semua kategori</option>
                        @foreach($categoryOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ann-filter__field">
                <label for="ann-start">Tanggal awal</label>
                <div class="ann-filter__control">
                    <input id="ann-start" type="date" name="start_date" value="{{ request('start_date') }}" max="{{ request('end_date') }}">
                </div>
            </div>
            <div class="ann-filter__field">
                <label for="ann-end">Tanggal akhir</label>
                <div class="ann-filter__control">
                    <input id="ann-end" type="date" name="end_date" value="{{ request('end_date') }}" min="{{ request('start_date') }}">
                </div>
            </div>
            <div class="ann-filter__actions">
                <button type="submit" class="ann-btn ann-btn--solid"><i class="fas fa-filter" aria-hidden="true"></i>Terapkan</button>
                @if($activeFilters)
                    <a href="{{ route('pengumuman.index') }}" class="ann-btn ann-btn--ghost"><i class="fas fa-rotate-left" aria-hidden="true"></i>Reset</a>
                @endif
            </div>
        </form>

        @if($activeFilters)
            <div class="ann-filter__chips" aria-label="Filter aktif">
                @foreach($activeFilters as $key => $label)
                    <a class="ann-chip" href="{{ route('pengumuman.index', array_diff_key(request()->query(), [$key => ''])) }}" title="Hapus filter ini">
                        <span>{{ \Illuminate\Support\Str::limit($label, 40) }}</span><i class="fas fa-xmark" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Announcements Section -->
    <section class="py-3" aria-labelledby="announcements-heading">
        <div class="flex justify-between items-center mb-4">
            <div class="text-sm text-base-content/70">
                @if($pengumumen->total() > 0)
                    Menampilkan {{ $pengumumen->firstItem() }} - {{ $pengumumen->lastItem() }} dari {{ $pengumumen->total() }} pengumuman
                @else
                    Tidak ditemukan pengumuman
                @endif
            </div>
        </div>

        @if($pengumumen->count() > 0)
            <div class="space-y-4">
                @foreach($pengumumen as $pengumuman)
                    <div class="card lg:card-side bg-base-100 shadow-xl announcement-card-modern" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="card-body">
                            <div class="flex items-center mb-2">
                                <div class="badge badge-category-green">{{ strtoupper($pengumuman->category ?? 'Umum') }}</div>
                            </div>
                            <h2 class="card-title">
                                <a href="{{ route('pengumuman.show', $pengumuman->id) }}" class="link link-hover announcement-title">
                                    {{ $pengumuman->title }}
                                </a>
                            </h2>
                            <p class="text-base-content/70">{{ \App\Support\RichText::plain($pengumuman->content, 150) }}</p>
                            <div class="card-actions justify-between items-center mt-4">
                                <div class="text-sm text-base-content/70 flex items-center">
                                    <i class="fas fa-calendar mr-1"></i> {{ $pengumuman->publish_date->format('d M Y') }}
                                    <span class="mx-2">|</span>
                                    <span class="flex items-center">
                                        <i class="fas fa-user mr-1"></i> {{ $pengumuman->authorName() }}
                                        @if($pengumuman->attachment || $pengumuman->url)
                                            <span class="flex gap-1 ml-2">
                                                @if($pengumuman->attachment)
                                                    <span class="badge badge-category-green">
                                                        <i class="fas fa-file-pdf text-white mr-1"></i> PDF
                                                    </span>
                                                @endif
                                                @if($pengumuman->url)
                                                    <span class="badge badge-category-green">
                                                        <i class="fas fa-link text-white mr-1"></i> URL
                                                    </span>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('pengumuman.show', $pengumuman->id) }}" class="btn btn-primary btn-sm">
                                    Baca Selengkapnya <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center">
                {{ $pengumumen->appends(request()->query())->links('vendor.pagination.daisyui') }}
            </div>
        @else
            <div class="text-center py-16" data-aos="fade-up">
                <div class="avatar mb-4">
                    <div class="w-24 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                        <i class="fas fa-bullhorn text-5xl"></i>
                    </div>
                </div>
                <h2 class="text-2xl font-bold mb-3">
                    Tidak Ada <span class="text-primary">Pengumuman</span>
                </h2>
                <p class="text-lg text-base-content/70 mb-4">
                    @if(request()->query())
                        Tidak ditemukan pengumuman dengan filter yang diterapkan
                    @else
                        Pengumuman akan ditampilkan di sini ketika tersedia
                    @endif
                </p>
                @if(request()->query())
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-primary">
                    <i class="fas fa-redo mr-2"></i>
                    Hapus Filter
                </a>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection