@extends('layouts.public')

@section('title', 'Arsip Pengumuman - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @vite(['resources/css/pengumuman.css'])
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
    <div class="card bg-base-100 shadow-xl mb-8" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" action="{{ route('pengumuman.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2">
                        <div class="form-control">
                            <label class="input-group">
                                <span><i class="fas fa-search"></i></span>
                                <input type="text" name="search" placeholder="Cari pengumuman..." class="input input-bordered w-full" value="{{ request('search') }}">
                            </label>
                        </div>
                    </div>
                    <div>
                        <select name="category" class="select select-bordered w-full">
                            <option value="">Semua Kategori</option>
                            <option value="akademik" {{ request('category') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="administrasi" {{ request('category') == 'administrasi' ? 'selected' : '' }}>Administrasi</option>
                            <option value="kegiatan" {{ request('category') == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                            <option value="lainnya" {{ request('category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="start_date" class="input input-bordered w-full" value="{{ request('start_date') }}" placeholder="Tanggal Awal">
                    </div>
                    <div>
                        <input type="date" name="end_date" class="input input-bordered w-full" value="{{ request('end_date') }}" placeholder="Tanggal Akhir">
                    </div>
                    <div class="col-span-1 flex">
                        <button type="submit" class="btn btn-primary w-full"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
                
                <!-- Reset and Active Filters -->
                @if(request()->query())
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('pengumuman.index') }}" class="btn btn-ghost btn-sm">
                        <i class="fas fa-redo mr-1"></i>
                        Reset Filter
                    </a>
                    <div class="text-sm text-base-content/70">Filter aktif:</div>
                    @if(request('search'))
                    <div class="badge badge-primary gap-2">
                        Cari: {{ request('search') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['search' => ''])) }}"><i class="fas fa-times"></i></a>
                    </div>
                    @endif
                    @if(request('category'))
                    <div class="badge badge-primary gap-2">
                        Kategori: {{ request('category') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['category' => ''])) }}"><i class="fas fa-times"></i></a>
                    </div>
                    @endif
                    @if(request('start_date'))
                    <div class="badge badge-primary gap-2">
                        Tgl Awal: {{ request('start_date') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['start_date' => ''])) }}"><i class="fas fa-times"></i></a>
                    </div>
                    @endif
                    @if(request('end_date'))
                    <div class="badge badge-primary gap-2">
                        Tgl Akhir: {{ request('end_date') }}
                        <a href="{{ route('pengumuman.index', array_diff_key(request()->query(), ['end_date' => ''])) }}"><i class="fas fa-times"></i></a>
                    </div>
                    @endif
                </div>
                @endif
            </form>
        </div>
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
                    <div class="card lg:card-side bg-base-100 shadow-xl" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="card-body">
                            <div class="flex items-center mb-2">
                                <div class="badge badge-primary">{{ $pengumuman->category ?? 'Umum' }}</div>
                            </div>
                            <h2 class="card-title">
                                <a href="{{ route('pengumuman.show', $pengumuman->id) }}" class="link link-hover">
                                    {{ $pengumuman->title }}
                                </a>
                            </h2>
                            <p class="text-base-content/70">{{ Str::limit(strip_tags($pengumuman->content), 150) }}</p>
                            <div class="card-actions justify-between items-center mt-4">
                                <div class="text-sm text-base-content/70">
                                    <i class="fas fa-calendar mr-1"></i> {{ $pengumuman->publish_date->format('d M Y') }}
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-user mr-1"></i> {{ $pengumuman->author ?? 'Admin' }}
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
                {{ $pengumuman->appends(request()->query())->links('vendor.pagination.daisyui') }}
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

    <!-- Call to Action -->
    <section class="py-16 mt-8 text-center" data-aos="fade-up">
        <div class="card bg-primary text-primary-content shadow-xl">
            <div class="card-body items-center text-center">
                <h2 class="card-title text-3xl font-bold">Ingin Mengetahui Lebih Banyak?</h2>
                <p class="text-lg">Jelajahi layanan kami atau lacak tiket yang telah Anda ajukan</p>
                <div class="card-actions justify-center mt-4">
                    <a href="{{ route('public.about') }}" class="btn btn-neutral">
                        <i class="fas fa-info-circle mr-2"></i>
                        Tentang Kami
                    </a>
                    <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-ghost">
                        <i class="fas fa-concierge-bell mr-2"></i>
                        Layanan Kami
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection