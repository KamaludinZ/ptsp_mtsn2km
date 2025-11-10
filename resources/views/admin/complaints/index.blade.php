@extends('layouts.admin')

@section('title', 'Daftar Pengaduan')

@push('styles')
<style>
    .complaint-card {
        transition: all 0.3s ease;
    }
    
    .complaint-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .priority-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .low-priority { background-color: #dbeafe; color: #1e40af; }
    .normal-priority { background-color: #d1fae5; color: #065f46; }
    .high-priority { background-color: #fef3c7; color: #92400e; }
    .urgent-priority { background-color: #fee2e2; color: #b91c1c; }
    
    .pending { background-color: #fef3c7; color: #92400e; }
    .in_review { background-color: #dbeafe; color: #1e40af; }
    .resolved { background-color: #d1fae5; color: #065f46; }
    .closed { background-color: #e5e7eb; color: #374151; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Daftar Pengaduan</h4>
                
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('suadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaduan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }}</h4>
                            <p class="mb-0 text-white">Total Pengaduan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count() }}</h4>
                            <p class="mb-0 text-white">Menunggu Ditinjau</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'resolved')->count() }}</h4>
                            <p class="mb-0 text-white">Terselesaikan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bullhorn fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white">{{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('type', 'saran')->count() }}</h4>
                            <p class="mb-0 text-white">Saran/Masukan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Daftar Pengaduan</h4>
                    <a href="{{ route('suadmin.complaints.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Pengaduan
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('suadmin.complaints.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_review" {{ request('status') == 'in_review' ? 'selected' : '' }}>Dalam Review</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Terselesaikan</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Jenis</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="">Semua Jenis</option>
                                    <option value="pengaduan" {{ request('type') == 'pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                                    <option value="saran" {{ request('type') == 'saran' ? 'selected' : '' }}>Saran</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="service_id" class="form-label">Layanan</label>
                                <select name="service_id" id="service_id" class="form-select">
                                    <option value="">Semua Layanan</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari pengaduan..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Complaints Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
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
                                        <span class="badge bg-{{ $complaint->type == 'pengaduan' ? 'danger' : 'success' }}">
                                            {{ ucfirst($complaint->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($complaint->subject, 30) }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($complaint->description, 50) }}</small>
                                    </td>
                                    <td>{{ $complaint->service ? $complaint->service->name : 'Tidak Ada' }}</td>
                                    <td>
                                        <span class="status-badge 
                                            {{ $complaint->status == 'pending' ? 'pending' : '' }}
                                            {{ $complaint->status == 'in_review' ? 'in_review' : '' }}
                                            {{ $complaint->status == 'resolved' ? 'resolved' : '' }}
                                            {{ $complaint->status == 'closed' ? 'closed' : '' }}
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge 
                                            {{ $complaint->priority == 'low' ? 'low-priority' : '' }}
                                            {{ $complaint->priority == 'normal' ? 'normal-priority' : '' }}
                                            {{ $complaint->priority == 'high' ? 'high-priority' : '' }}
                                            {{ $complaint->priority == 'urgent' ? 'urgent-priority' : '' }}
                                        ">
                                            {{ ucfirst($complaint->priority) }}
                                        </span>
                                    </td>
                                    <td>{{ $complaint->assignedTo ? $complaint->assignedTo->name : 'Belum Ditugaskan' }}</td>
                                    <td>{{ $complaint->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('suadmin.complaints.show', $complaint) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('suadmin.complaints.edit', $complaint) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('suadmin.complaints.destroy', $complaint) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengaduan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data pengaduan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                        <div class="mb-2 mb-md-0">
                            <p class="mb-0">
                                Menampilkan {{ $complaints->firstItem() }} sampai {{ $complaints->lastItem() }} 
                                dari {{ $complaints->total() }} pengaduan
                            </p>
                        </div>
                        <div>
                            {{ $complaints->withQueryString()->links() }}
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ \App\Models\Complaint::where('type', 'pengaduan')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }} Pengaduan
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    {{ \App\Models\Complaint::where('type', 'saran')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count() }} Saran
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <i class="fas fa-comments me-1"></i>
                                    {{ \App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count() }} Menunggu
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection