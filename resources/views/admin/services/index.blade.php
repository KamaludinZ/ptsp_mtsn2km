@extends('layouts.admin')

@section('title', 'Manajemen Layanan')

@push('styles')
<style>
    /* Stats Card Improvements - Light Mode */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #14532d 0%, #16a34a 100%) !important;
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }

    .card {
        border-radius: 12px !important;
    }

    .text-white {
        color: #ffffff !important;
    }

    .text-white-opacity-75 {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .bg-white {
        background-color: rgba(255, 255, 255, 0.25) !important;
    }

    .bg-opacity-25 {
        background-color: rgba(255, 255, 255, 0.25) !important;
    }

    /* Stats Card Styles */
    .stats-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: none !important;
    }

    .stats-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
    }

    /* Table Row Hover Effects */
    .table-hover tbody tr {
        transition: all 0.3s ease;
    }

    .table-hover tbody tr:hover {
        background-color: var(--bs-gray-100) !important;
        transform: scale(1.005);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* Service Icon in Table */
    .service-icon {
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
        color: #fff;
        transition: all 0.3s ease;
    }

    .table-hover tbody tr:hover .service-icon {
        transform: scale(1.1) rotate(5deg);
    }

    /* Badge Improvements */
    .badge {
        font-weight: 600;
        padding: 0.5em 0.8em;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Slug Badge */
    .badge-slug {
        background-color: var(--bs-gray-200) !important;
        color: var(--bs-gray-700) !important;
        font-family: 'Courier New', monospace;
        text-transform: none;
        letter-spacing: 0;
    }

    /* Category Badges */
    .badge-category {
        background-color: var(--bs-primary) !important;
        color: #fff !important;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
    }

    /* Button Group Hover */
    .btn-group .btn {
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: scale(1.05);
        z-index: 2;
    }

    /* Card Header Improvements */
    .card-header {
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .card:hover .card-header {
        background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    }

    /* Filter Section */
    .form-select, .form-control {
        transition: all 0.3s ease;
        border-color: var(--bs-border);
    }

    .form-select:focus, .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(20, 83, 45, 0.15);
    }

    /* Dark Mode Adaptations */
    [data-theme="dark"] .bg-gradient-primary {
        background: linear-gradient(135deg, #166534 0%, #22c55e 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-success {
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-info {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
    }

    [data-theme="dark"] .stats-card:hover {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5) !important;
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--bs-gray-700) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .service-icon {
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    }

    [data-theme="dark"] .badge-slug {
        background-color: var(--bs-gray-700) !important;
        color: var(--bs-gray-300) !important;
    }

    [data-theme="dark"] .badge-category {
        background-color: #166534 !important;
    }

    [data-theme="dark"] .card-header {
        background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    }

    [data-theme="dark"] .card:hover .card-header {
        background: linear-gradient(135deg, #7c2d12 0%, #c2410c 100%);
    }

    [data-theme="dark"] .form-select:focus,
    [data-theme="dark"] .form-control:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.15);
    }

    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
    }

    .empty-state i {
        transition: all 0.3s ease;
    }

    .empty-state:hover i {
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-2 fw-bold" style="color: var(--bs-text);">
                        <i class="bi bi-gear-fill me-2" style="color: var(--bs-secondary);"></i>Manajemen Layanan
                    </h2>
                    <p class="text-muted mb-0">Kelola semua layanan yang tersedia di sistem PTSP</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('suadmin.services.create') }}" class="btn btn-primary shadow-sm">
                        <i class="bi bi-plus-circle-fill me-2"></i>Tambah Layanan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Total Layanan</h6>
                            <h3 class="mb-0" style="color: #ffffff !important;">{{ $totalServices }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Jumlah layanan keseluruhan</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="fas fa-layer-group fa-2x" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Layanan Aktif</h6>
                            <h3 class="mb-0" style="color: #ffffff !important;">{{ $activeServices }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Layanan yang sedang aktif</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="fas fa-check-circle fa-2x" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Layanan Online</h6>
                            <h3 class="mb-0" style="color: #ffffff !important;">{{ $onlineServices }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Layanan dengan mode online</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="fas fa-globe-americas fa-2x" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Rata-rata Hari</h6>
                            <h3 class="mb-0" style="color: #ffffff !important;">{{ number_format($avgEstimatedDays, 1) }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Durasi rata-rata penyelesaian</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="fas fa-clock fa-2x" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-amber-600 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i> Daftar Layanan</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header">Status Layanan</h6></li>
                                <li><a class="dropdown-item" href="?status=active">Hanya Aktif</a></li>
                                <li><a class="dropdown-item" href="?status=inactive">Hanya Tidak Aktif</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Mode Layanan</h6></li>
                                <li><a class="dropdown-item" href="?mode=online">Mode Online</a></li>
                                <li><a class="dropdown-item" href="?mode=offline">Mode Offline</a></li>
                                <li><a class="dropdown-item" href="?mode=both">Mode Both</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Approval</h6></li>
                                <li><a class="dropdown-item" href="?approval=required">Memerlukan Approval</a></li>
                                <li><a class="dropdown-item" href="?approval=not_required">Tidak Perlu Approval</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('suadmin.services.index') }}">Tampilkan Semua</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> 
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kategori</label>
                            <select class="form-select shadow-sm" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Cari Layanan</label>
                            <div class="input-group shadow-sm">
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari nama layanan..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100 shadow-sm" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Terapkan Filter
                            </button>
                        </div>
                    </div>

                    <!-- Services Table -->
                    <div class="table-responsive rounded-3 shadow-sm">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr class="border-top border-bottom">
                                    <th>Nama Layanan</th>
                                    <th>Slug</th>
                                    <th>Kategori</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th>Digital</th>
                                    <th>Approval</th>
                                    <th>Estimasi (Hari)</th>
                                    <th>Dibuat</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr class="align-middle border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="service-icon">
                                                        <i class="fas fa-concierge-bell"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold" style="color: var(--bs-text);">{{ $service->name }}</h6>
                                                    <small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-slug">{{ $service->slug }}</span></td>
                                        <td>
                                            @foreach($service->categories as $category)
                                                <span class="badge badge-category">{{ $category->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php
                                                $modeConfig = [
                                                    'online' => ['color' => 'success', 'icon' => 'cloud'],
                                                    'offline' => ['color' => 'warning', 'icon' => 'store'],
                                                    'both' => ['color' => 'info', 'icon' => 'sync']
                                                ];
                                                $config = $modeConfig[$service->mode] ?? ['color' => 'secondary', 'icon' => 'question'];
                                            @endphp
                                            <span class="badge bg-{{ $config['color'] }}">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                {{ ucfirst($service->mode) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($service->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i> Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i> Tidak Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->is_digital_product)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-file-pdf me-1"></i> Ya
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-box me-1"></i> Tidak
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->approval_required ?? true)
                                                <span class="badge bg-primary" data-bs-toggle="tooltip" title="{{ $service->approval_instructions ?? 'Memerlukan persetujuan dari petugas' }}">
                                                    <i class="bi bi-shield-check me-1"></i> Perlu Approval
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-shield-slash me-1"></i> Tidak Perlu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>{{ $service->estimated_days ?? 'N/A' }} hari
                                            </span>
                                        </td>
                                        <td style="color: var(--bs-text);">{{ $service->created_at->format('d M Y') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('suadmin.services.show', $service) }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="Lihat detail layanan">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('suadmin.services.edit', $service) }}"
                                                   class="btn btn-outline-warning btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="Edit layanan">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('suadmin.services.destroy', $service) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="Hapus layanan">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center empty-state">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Tidak Ada Layanan Ditemukan</h5>
                                                <p class="text-muted">Silakan tambahkan layanan baru atau sesuaikan filter pencarian Anda</p>
                                                <a href="{{ route('suadmin.services.create') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus me-2"></i>Tambah Layanan
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $services->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
        const category = document.getElementById('categoryFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = '{{ route('suadmin.services.index') }}';
        const params = [];
        
        if (category) params.push('category=' + category);
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Also trigger filter when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });
    
    // Also trigger filter when click search button
    document.getElementById('searchButton').addEventListener('click', function() {
        document.getElementById('applyFilters').click();
    });
});
</script>
@endsection