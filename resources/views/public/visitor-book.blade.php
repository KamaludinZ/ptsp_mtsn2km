@extends('layouts.public')

@section('title', 'Buku Tamu - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@section('content')

<!-- Page Header -->
<div class="hero min-h-[40vh] bg-primary">
    <div class="hero-overlay bg-opacity-60"></div>
    <div class="hero-content text-center text-neutral-content">
        <div class="max-w-md">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ url('/') }}">Beranda</a></li> 
                    <li>Buku Tamu</li>
                </ul>
            </div>
            <h1 class="mb-5 text-5xl font-bold">Buku Tamu</h1>
            <p class="mb-5">Pencatatan kunjungan tamu fisik ke MTsN 2 Kota Malang</p>
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title">Total Tamu Hari Ini</div>
                    <div class="stat-value">{{ $visitors->total() }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Sedang Aktif</div>
                    <div class="stat-value">{{ $visitors->whereNull('checkout_at')->count() }}</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Tanggal</div>
                    <div class="stat-value">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4 pb-5">
    <!-- Form Tabs -->
    <div class="tabs tabs-boxed">
        <a class="tab tab-lg" :class="{ 'tab-active': activeTab === 'visitor' }" @click.prevent="activeTab = 'visitor'">Formulir Tamu Kunjungan</a> 
        <a class="tab tab-lg" :class="{ 'tab-active': activeTab === 'applicant' }" @click.prevent="activeTab = 'applicant'">Formulir Tamu Pemohon Layanan Offline</a>
    </div>

    <div class="card bg-base-100 shadow-xl mt-[-1rem]">
        <div class="card-body">
            <!-- Visitor Form -->
            <div x-show="activeTab === 'visitor'">
                <form action="#" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">
                                <span class="label-text">Nama Lengkap *</span>
                            </label>
                            <input type="text" name="name" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">No. Telepon/HP *</span>
                            </label>
                            <input type="tel" name="phone" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Instansi/Perusahaan</span>
                            </label>
                            <input type="text" name="institution" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Tujuan Kunjungan *</span>
                            </label>
                            <input type="text" name="purpose" class="input input-bordered w-full" required>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="label">
                                <span class="label-text">Keperluan Lainnya</span>
                            </label>
                            <textarea name="notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <div class="form-control">
                                <label class="label cursor-pointer">
                                    <span class="label-text">Samarkan Nama di Daftar Tamu</span> 
                                    <input type="checkbox" name="obscure_name" class="checkbox" />
                                </label>
                            </div>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <button type="submit" class="btn btn-primary">Daftar Tamu</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Service Applicant Form -->
<div x-show="activeTab === 'applicant'">
                <form action="{{ route('public.applicant.submit') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">
                                <span class="label-text">Nama Lengkap *</span>
                            </label>
                            <input type="text" name="name" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">No. Telepon/HP *</span>
                            </label>
                            <input type="tel" name="phone" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Instansi/Perusahaan</span>
                            </label>
                            <input type="text" name="institution" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Status Pemohon</span>
                            </label>
                            <select name="applicant_type" class="select select-bordered w-full">
                                <option value="">Pilih Status</option>
                                <option value="siswa">Siswa</option>
                                <option value="wali_murid">Wali Murid</option>
                                <option value="alumni">Alumni</option>
                                <option value="pegawai">Pegawai</option>
                                <option value="umum">Masyarakat Umum</option>
                                <option value="instansi">Instansi Pemerintah</option>
                            </select>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="label">
                                <span class="label-text">Layanan yang Dituju *</span>
                            </label>
                            <input type="text" name="target_service" class="input input-bordered w-full" required placeholder="Contoh: Pengambilan Ijazah, Surat Keterangan, dll">
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="label">
                                <span class="label-text">Catatan Tambahan</span>
                            </label>
                            <textarea name="notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <div class="form-control">
                                <label class="label cursor-pointer">
                                    <span class="label-text">Samarkan Nama di Daftar Tamu</span> 
                                    <input type="checkbox" name="obscure_name" class="checkbox" />
                                </label>
                            </div>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <button type="submit" class="btn btn-primary">Daftar Pemohon Layanan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Visitor List -->
    <div class="card bg-base-100 shadow-xl mt-8">
        <div class="card-body">
            <h2 class="card-title">
                <i class="fas fa-list mr-2"></i>Daftar Tamu 
                <span class="text-primary">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
            </h2>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Tamu</th>
                            <th>Instansi</th>
                            <th>Tujuan</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $index => $visitor)
                            <tr>
                                <th>{{ ($visitors->currentPage() - 1) * $visitors->perPage() + $index + 1 }}</th>
                                <td>
                                    <div class="font-bold">
                                        @if ($visitor->is_obscured)
                                            <script>document.write(obscureName("{{ $visitor->name }}"))</script>
                                        @else
                                            {{ $visitor->name }}
                                        @endif
                                    </div>
                                    <div class="text-sm opacity-50">{{ $visitor->phone }}</div>
                                </td>
                                <td>{{ $visitor->institution ?: '-' }}</td>
                                <td>{{ Str::limit($visitor->purpose, 30) }}</td>
                                <td>{{ \Carbon\Carbon::parse($visitor->created_at)->format('H:i') }}</td>
                                <td>{{ $visitor->checkout_at ? \Carbon\Carbon::parse($visitor->checkout_at)->format('H:i') : '-' }}</td>
                                <td>
                                    @if ($visitor->checkout_at)
                                        <div class="badge badge-warning gap-2">
                                            <i class="fas fa-check-circle"></i>Selesai
                                        </div>
                                    @else
                                        <div class="badge badge-success gap-2">
                                            <i class="fas fa-circle"></i>Aktif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-16">
                                    <div class="avatar mb-4">
                                        <div class="w-24 rounded-full bg-base-200 text-base-content/50 flex items-center justify-center">
                                            <i class="fas fa-users text-5xl"></i>
                                        </div>
                                    </div>
                                    <h4 class="text-xl font-bold">Belum Ada Tamu</h4>
                                    <p>Belum ada data tamu untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            @if ($visitors->hasPages())
                <div class="card-actions justify-center mt-4">
                    {{ $visitors->links('vendor.pagination.daisyui') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Function to obscure a name (corrected)
    function obscureName(name) {
        if (!name) return '';
        const parts = name.split(' ');
        return parts.map(part => {
            if (part.length <= 2) return part;
            return part.charAt(0) + '*'.repeat(part.length - 2) + part.charAt(part.length - 1);
        }).join(' ');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the active tab
        const activeTab = localStorage.getItem('activeTab') || '#visitor-form';
        if(activeTab === '#applicant-form') {
            const applicantTab = document.getElementById('applicant-tab');
            if(applicantTab) {
                applicantTab.click();
            }
        }
        
        // Save the active tab in localStorage
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>

@push('styles')
    @vite(['resources/css/visitor-book.css'])
@endpush
@endsection