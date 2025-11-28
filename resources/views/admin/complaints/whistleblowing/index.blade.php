@extends('layouts.admin')

@section('title', 'Daftar Laporan Whistleblowing')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Daftar Laporan Whistleblowing</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('suadmin.dashboard') }}">Dashboard</a></li>
                    <li>Whistleblowing</li>
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
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where('complaint_type', 'whistleblowing')->count() }}</h4>
                        <p class="mb-0">Total Laporan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-warning text-warning-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('status', 'pending')->count() }}</h4>
                        <p class="mb-0">Menunggu Ditinjau</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-success text-success-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('status', 'resolved')->count() }}</h4>
                        <p class="mb-0">Terselesaikan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-info text-info-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-user-secret fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold">{{ \App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('is_anonymous', true)->count() }}</h4>
                        <p class="mb-0">Anonim</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Daftar Laporan Whistleblowing</h4>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('suadmin.whistleblowing.index') }}" class="mb-4">
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
                        <label for="priority" class="label">
                            <span class="label-text">Prioritas</span>
                        </label>
                        <select name="priority" id="priority" class="select select-bordered w-full">
                            <option value="">Semua Prioritas</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari laporan..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <!-- Whistleblowing Reports Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Laporan</th>
                            <th>Subjek</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Anonim</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($whistleblowingReports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $report->complaint_number }}</td>
                            <td>
                                <strong>{{ Str::limit($report->subject, 50) }}</strong><br>
                                <span class="text-sm opacity-50">{{ Str::limit($report->description, 70) }}</span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($report->status == 'pending') badge-warning
                                    @elseif($report->status == 'in_review') badge-info
                                    @elseif($report->status == 'resolved') badge-success
                                    @else badge-neutral @endif">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($report->priority == 'low') badge-success
                                    @elseif($report->priority == 'normal') badge-info
                                    @elseif($report->priority == 'high') badge-warning
                                    @else badge-error @endif">
                                    {{ ucfirst($report->priority) }}
                                </span>
                            </td>
                            <td>
                                @if($report->is_anonymous)
                                    <span class="badge badge-info">Ya</span>
                                @else
                                    <span class="badge badge-neutral">Tidak</span>
                                @endif
                            </td>
                            <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                        <li>
                                            <a href="{{ route('suadmin.whistleblowing.show', $report) }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('suadmin.whistleblowing.edit', $report) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('suadmin.complaints.destroy', $report) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
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
                            <td colspan="8" class="text-center py-16">
                                <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                <h5 class="text-lg font-bold text-base-content/70">Tidak ada laporan whistleblowing ditemukan.</h5>
                                <p class="text-base-content/50">Silakan sesuaikan filter pencarian Anda</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                {{ $whistleblowingReports->links('vendor.pagination.daisyui') }}
            </div>
        </div>
    </div>
</div>
@endsection