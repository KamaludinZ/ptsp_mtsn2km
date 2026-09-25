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

    /* Filter bar */
    .ann-filter { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.25rem; margin-bottom: 2rem; box-shadow: 0 1px 2px rgba(15,23,42,.05); }
    .ann-filter__form { display: grid; gap: 1rem; grid-template-columns: 1fr; align-items: end; }
    .ann-filter__field label { display: block; margin-bottom: .375rem; font-size: .8125rem; font-weight: 600; color: #374151; }
    .ann-filter__control { position: relative; display: flex; align-items: center; }
    .ann-filter__control > i { position: absolute; left: .875rem; color: #6b7280; pointer-events: none; font-size: .85rem; }
    .ann-filter__control input,
    .ann-filter__control select { width: 100%; height: 2.75rem; padding: 0 .875rem; border: 1px solid #d1d5db; border-radius: 10px; background-color: #fff; color: #111827; font-size: .9rem; line-height: 1; transition: border-color .15s, box-shadow .15s; }
    .ann-filter__control > i + input { padding-left: 2.4rem; }
    .ann-filter__control select { appearance: none; -webkit-appearance: none; padding-right: 2.25rem; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .875rem center; }
    .ann-filter__control input:hover,
    .ann-filter__control select:hover { border-color: #9ca3af; }
    .ann-filter__control input:focus,
    .ann-filter__control select:focus { outline: none; border-color: #166534; box-shadow: 0 0 0 3px rgba(22,101,52,.18); }
    .ann-filter__actions { display: flex; gap: .5rem; }
    .ann-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; height: 2.75rem; padding: 0 1.1rem; border-radius: 10px; border: 1px solid #166534; font-size: .9rem; font-weight: 600; white-space: nowrap; text-decoration: none; cursor: pointer; transition: background-color .15s, color .15s; }
    .ann-btn--solid { flex: 1; background: #166534; color: #fff; }
    .ann-btn--solid:hover { background: #14532d; color: #fff; }
    .ann-btn--ghost { background: transparent; color: #166534; border-color: #d1d5db; }
    .ann-btn--ghost:hover { background: #ecfdf3; border-color: #166534; color: #166534; }
    .ann-btn:focus-visible, .ann-chip:focus-visible { outline: 2px solid #ea580c; outline-offset: 2px; }
    .ann-filter__chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; }
    .ann-chip { display: inline-flex; align-items: center; gap: .5rem; padding: .3rem .7rem; border-radius: 999px; background: #ecfdf3; color: #14532d; border: 1px solid #bbf7d0; font-size: .8125rem; font-weight: 500; text-decoration: none; }
    .ann-chip:hover { background: #dcfce7; color: #14532d; }
    @media (min-width: 640px) { .ann-filter__form { grid-template-columns: repeat(2, 1fr); } .ann-filter__field--search, .ann-filter__actions { grid-column: 1 / -1; } .ann-btn--solid { flex: 0 0 auto; } .ann-filter__actions { justify-content: flex-end; } }
    @media (min-width: 1024px) { .ann-filter__form { grid-template-columns: minmax(0, 2fr) minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 1fr) auto; } .ann-filter__field--search { grid-column: auto; } .ann-filter__actions { grid-column: auto; } }

    [data-theme="dark"] .ann-filter { background: #1f2937; border-color: #374151; }
    [data-theme="dark"] .ann-filter__field label { color: #e5e7eb; }
    [data-theme="dark"] .ann-filter__control input,
    [data-theme="dark"] .ann-filter__control select { background-color: #111827; border-color: #4b5563; color: #f3f4f6; color-scheme: dark; }
    [data-theme="dark"] .ann-btn--solid { background: #22c55e; border-color: #22c55e; color: #052e16; }
    [data-theme="dark"] .ann-btn--ghost { color: #86efac; border-color: #4b5563; }
    [data-theme="dark"] .ann-chip { background: rgba(34,197,94,.14); border-color: rgba(34,197,94,.35); color: #bbf7d0; }
    [data-theme="dark"] .ann-filter__chips { border-color: #374151; }

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
        <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
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
        $categoryOptions = ['akademik' => 'Akademik', 'administrasi' => 'Administrasi', 'kegiatan' => 'Kegiatan', 'lainnya' => 'Lainnya'];
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
                            <p class="text-base-content/70">{{ Str::limit(strip_tags($pengumuman->content), 150) }}</p>
                            <div class="card-actions justify-between items-center mt-4">
                                <div class="text-sm text-base-content/70 flex items-center">
                                    <i class="fas fa-calendar mr-1"></i> {{ $pengumuman->publish_date->format('d M Y') }}
                                    <span class="mx-2">|</span>
                                    <span class="flex items-center">
                                        <i class="fas fa-user mr-1"></i> {{ $pengumuman->author ?? 'Admin' }}
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