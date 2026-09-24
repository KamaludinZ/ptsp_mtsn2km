@extends('layouts.admin')

@section('title', 'Daftar Pengaduan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Daftar Pengaduan</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li>Pengaduan</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card bg-primary text-primary-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-comments fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }}</h4>
                        <p class="mb-0">Total Pengaduan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-warning text-warning-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count() }}</h4>
                        <p class="mb-0">Menunggu Ditinjau</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-success text-success-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'resolved')->count() }}</h4>
                        <p class="mb-0">Terselesaikan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-info text-info-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-bullhorn fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('complaint_type', 'suggestion')->count() }}</h4>
                        <p class="mb-0">Saran/Masukan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-header bg-base-200 text-base-content">
            <h4 class="card-title mb-0">Daftar Pengaduan</h4>
            <a href="{{ route('admin.complaints.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Pengaduan
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.complaints.index') }}" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" id="status" class="select select-bordered w-full">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_review" {{ request('status') == 'in_review' ? 'selected' : '' }}>Dalam Review</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Terselesaikan</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                    </div>
                    <div>
                        <label for="type" class="label">
                            <span class="label-text">Jenis</span>
                        </label>
                        <select name="type" id="type" class="select select-bordered w-full">
                            <option value="">Semua Jenis</option>
                            <option value="pengaduan" {{ request('type') == 'pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                            <option value="saran" {{ request('type') == 'saran' ? 'selected' : '' }}>Saran</option>
                        </select>
                    </div>
                    <div>
                        <label for="service_id" class="label">
                            <span class="label-text">Layanan</span>
                        </label>
                        <select name="service_id" id="service_id" class="select select-bordered w-full">
                            <option value="">Semua Layanan</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari pengaduan..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Complaints Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Pengaduan</th>
                            <th>Jenis</th>
                            <th>Subjek</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Ditugaskan Ke</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $complaint->complaint_number }}</td>
                            <td>
                                <span class="badge 
                                    @if($complaint->type == 'pengaduan') badge-error
                                    @else badge-success @endif">
                                    {{ ucfirst($complaint->type) }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ Str::limit($complaint->subject, 30) }}</strong><br>
                                <span class="text-sm opacity-50">{{ Str::limit($complaint->description, 50) }}</span>
                            </td>
                            <td>{{ $complaint->service ? $complaint->service->name : 'Tidak Ada' }}</td>
                            <td>
                                <span class="badge 
                                    @if($complaint->status == 'pending') badge-warning
                                    @elseif($complaint->status == 'in_review') badge-info
                                    @elseif($complaint->status == 'resolved') badge-success
                                    @else badge-neutral @endif">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($complaint->priority == 'low') badge-success
                                    @elseif($complaint->priority == 'normal') badge-info
                                    @elseif($complaint->priority == 'high') badge-warning
                                    @else badge-error @endif">
                                    {{ ucfirst($complaint->priority) }}
                                </span>
                            </td>
                            <td>{{ $complaint->assignedTo ? $complaint->assignedTo->name : 'Belum Ditugaskan' }}</td>
                            <td>{{ $complaint->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                        <li>
                                            <a href="{{ route('admin.complaints.show', $complaint) }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.complaints.edit', $complaint) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.complaints.destroy', $complaint) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengaduan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-16">
                                <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                <h5 class="text-lg font-bold text-base-content/70">Tidak ada data pengaduan.</h5>
                                <p class="text-base-content/50">Silakan tambahkan pengaduan baru atau sesuaikan filter pencarian Anda</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-4">
                <div class="mb-2 md:mb-0">
                    <p class="mb-0 text-base-content/70">
                        Menampilkan {{ $complaints->firstItem() }} sampai {{ $complaints->lastItem() }} 
                        dari {{ $complaints->total() }} pengaduan
                    </p>
                </div>
                <div>
                    {{ $complaints->withQueryString()->links('vendor.pagination.daisyui') }}
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="flex flex-wrap justify-center gap-2 mt-3">
                <div class="badge badge-outline badge-primary">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ \App\Models\Complaint::where('complaint_type', 'complaint')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }} Pengaduan
                </div>
                <div class="badge badge-outline badge-success">
                    <i class="fas fa-lightbulb mr-1"></i>
                    {{ \App\Models\Complaint::where('complaint_type', 'suggestion')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }} Saran
                </div>
                <div class="badge badge-outline badge-info">
                    <i class="fas fa-comments mr-1"></i>
                    {{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count() }} Menunggu
                </div>
            </div>
        </div>
    </div>
</div>
@endsection