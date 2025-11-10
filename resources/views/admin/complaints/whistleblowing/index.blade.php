@extends('layouts.admin')

@section('title', 'Manajemen Whistleblowing - ' . config('app.name'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">🛡️ Manajemen Whistleblowing</h1>
            <p class="text-muted mb-0">Kelola laporan pelanggaran dan tindakan tidak etis</p>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-white mb-1 opacity-75" style="font-size: 0.75rem; font-weight: 600;">Total Laporan</h6>
                            <h3 class="mb-0 text-white fw-bold">{{ number_format($whistleblowingReports->total()) }}</h3>
                            <p class="text-white mb-0 opacity-75" style="font-size: 0.8rem;">Jumlah laporan whistleblowing</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-shield-alt text-white fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-white mb-1 opacity-75" style="font-size: 0.75rem; font-weight: 600;">Belum Diproses</h6>
                            <h3 class="mb-0 text-white fw-bold">{{ $whistleblowingReports->where('status', 'pending')->count() }}</h3>
                            <p class="text-white mb-0 opacity-75" style="font-size: 0.8rem;">Laporan menunggu verifikasi</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-clock text-white fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-white mb-1 opacity-75" style="font-size: 0.75rem; font-weight: 600;">Diproses</h6>
                            <h3 class="mb-0 text-white fw-bold">{{ $whistleblowingReports->where('status', 'processing')->count() }}</h3>
                            <p class="text-white mb-0 opacity-75" style="font-size: 0.8rem;">Laporan sedang ditangani</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-cogs text-white fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-white mb-1 opacity-75" style="font-size: 0.75rem; font-weight: 600;">Selesai</h6>
                            <h3 class="mb-0 text-white fw-bold">{{ $whistleblowingReports->where('status', 'completed')->count() }}</h3>
                            <p class="text-white mb-0 opacity-75" style="font-size: 0.8rem;">Laporan ditindaklanjuti</p>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-check-circle text-white fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari laporan...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="processing">Diproses</option>
                        <option value="completed">Selesai</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" placeholder="Tanggal Awal">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Whistleblowing Reports Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-shield-alt me-2"></i>Daftar Laporan Whistleblowing</h5>
            </div>
        </div>
        <div class="card-body">
            @if($whistleblowingReports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">No Tiket</th>
                                <th width="25%">Judul</th>
                                <th width="20%">Pelapor</th>
                                <th width="15%">Tanggal</th>
                                <th width="10%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($whistleblowingReports as $report)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $report->complaint_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $report->title }}</div>
                                    <small class="text-muted">{{ Str::limit($report->description, 60) }}</small>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-user-secret text-warning me-1"></i>
                                        {{ $report->reporter_name }}
                                    </div>
                                    <small class="text-muted">{{ $report->email ?? '-' }}</small>
                                </td>
                                <td>{{ $report->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($report->status == 'pending') bg-warning-subtle text-warning
                                        @elseif($report->status == 'processing') bg-info-subtle text-info
                                        @elseif($report->status == 'completed') bg-success-subtle text-success
                                        @else bg-danger-subtle text-danger @endif">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('suadmin.whistleblowing.show', $report->id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Lihat
                                        </a>
                                        <a href="{{ route('suadmin.whistleblowing.show', $report->id) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-check me-1"></i> Tangani
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <p class="mb-0">
                            Menampilkan {{ $whistleblowingReports->firstItem() }} sampai {{ $whistleblowingReports->lastItem() }} 
                            dari {{ $whistleblowingReports->total() }} laporan
                        </p>
                    </div>
                    <div>
                        {{ $whistleblowingReports->links() }}
                    </div>
                </div>
                
                <!-- Additional Info -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning">
                                <i class="fas fa-clock me-1"></i>
                                {{ \App\Models\Complaint::where(function($q) { $q->where('complaint_type', 'whistleblowing')->orWhere('is_whistleblowing', true); })->where('status', 'pending')->count() }} Menunggu
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info">
                                <i class="fas fa-cogs me-1"></i>
                                {{ \App\Models\Complaint::where(function($q) { $q->where('complaint_type', 'whistleblowing')->orWhere('is_whistleblowing', true); })->where('status', 'processing')->count() }} Diproses
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ \App\Models\Complaint::where(function($q) { $q->where('complaint_type', 'whistleblowing')->orWhere('is_whistleblowing', true); })->where('status', 'completed')->count() }} Selesai
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-secret fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Ada Laporan Whistleblowing</h5>
                    <p class="text-muted">Laporan whistleblowing akan ditampilkan di sini ketika tersedia</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection