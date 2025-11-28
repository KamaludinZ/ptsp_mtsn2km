@extends('layouts.public')

@section('title', 'Lacak Pengaduan - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Service Component Hover Effect */
    .service-component {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.25) !important;
        backdrop-filter: blur(10px);
    }

    .service-component:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        background: rgba(255, 255, 255, 0.35) !important;
    }

    .icon-hover {
        transition: transform 0.3s ease;
    }

    .service-component:hover .icon-hover {
        transform: scale(1.2) rotate(5deg);
    }

    /* Dark mode support for stat cards */
    [data-theme="dark"] .stat-card {
        background-color: var(--bs-surface) !important;
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] .stat-card .card {
        background-color: var(--bs-surface) !important;
    }

    /* Dark mode for additional stats */
    [data-theme="dark"] .stats-additional {
        background-color: var(--bs-surface) !important;
        border-color: var(--bs-primary) !important;
    }

    /* Dark mode for performance section */
    [data-theme="dark"] .performance-section {
        background-color: var(--bs-surface) !important;
    }

    /* Komponen text always white on green gradient */
    .component-text {
        color: white !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
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
    
    /* Custom styles for complaint tracking result page */
    .page-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
        padding: 48px 0;
        margin-bottom: 32px;
    }

    .breadcrumb {
        font-size: 14px;
        margin-bottom: 16px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
    }

    .page-subtitle {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
    }

    .result-section {
        background: #ffffff;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
    }
    
    [data-theme="dark"] .result-section {
        background: #111827;
    }
    
    .complaint-card {
        background: var(--bs-surface);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    [data-theme="dark"] .complaint-card {
        background: #1f2937;
    }
    
    .detail-card {
        background: var(--bs-surface);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 16px;
    }
    
    [data-theme="dark"] .detail-card {
        background: #1f2937;
    }
    
    .status-badge {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #92400e;
    }

    .status-processing {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
    }

    [data-theme="dark"] .status-pending {
        color: #fde68a;
    }

    [data-theme="dark"] .status-processing {
        color: #93c5fd;
    }

    [data-theme="dark"] .status-completed {
        color: #bbf7d0;
    }

    .timeline-step {
        display: flex;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    
    .timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 12px;
    }

    .timeline-content {
        flex: 1;
    }
    
    .btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
    }
    
    .btn-primary {
        background: var(--bs-primary);
        color: #ffffff !important;
    }
    
    .btn-primary:hover {
        background: var(--bs-primary-dark);
    }
    
    .btn-outline {
        background: transparent;
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary);
    }
    
    .btn-outline:hover {
        background: var(--bs-primary);
        color: #ffffff;
    }
    
    .btn-secondary {
        background: var(--bs-gray-600);
        color: #ffffff !important;
    }
    
    .btn-secondary:hover {
        background: var(--bs-gray-700);
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ url('/') }}" style="color: rgba(255,255,255,0.8);">Beranda</a>
            <span style="color: rgba(255,255,255,0.6); margin: 0 8px;">/</span>
            <a href="{{ route('supervision.complaint.track.form') }}" style="color: rgba(255,255,255,0.8);">Lacak Pengaduan</a>
            <span style="color: rgba(255,255,255,0.6); margin: 0 8px;">/</span>
            <span style="color: #ffffff;">Hasil Pelacakan</span>
        </nav>

        <!-- Title -->
        <h1 class="page-title">Status Pengaduan</h1>
        <p class="page-subtitle">Informasi terkini tentang pengaduan Anda</p>
    </div>
</div>

<!-- Main Content -->
<div class="container" style="padding: 0 16px 64px;">
    <!-- Tracking Result Section -->
    <div class="result-section">
        <!-- Complaint Card -->
        <div class="complaint-card mb-4">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="text-muted mb-1" style="font-size: 0.875rem; color: var(--bs-secondary-text);">Nomor Tiket</p>
                    <p class="h5 fw-bold mb-0" style="color: var(--bs-primary);">#{{ $complaint->complaint_number }}</p>
                </div>
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="text-muted mb-1" style="font-size: 0.875rem; color: var(--bs-secondary-text);">Jenis Pengaduan</p>
                    <p class="h5 mb-0" style="color: var(--bs-text);">
                        {{ ucfirst(str_replace('_', ' ', $complaint->complaint_type)) }}
                    </p>
                </div>
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="text-muted mb-1" style="font-size: 0.875rem; color: var(--bs-secondary-text);">Status</p>
                    <span class="status-badge 
                        @if($complaint->status === 'pending') status-pending
                        @elseif($complaint->status === 'processing') status-processing
                        @elseif($complaint->status === 'completed') status-completed
                        @else status-pending @endif">
                        @if($complaint->status === 'pending')
                            <i class="fas fa-clock me-1"></i>Menunggu
                        @elseif($complaint->status === 'processing')
                            <i class="fas fa-cog me-1"></i>Diproses
                        @elseif($complaint->status === 'completed')
                            <i class="fas fa-check-circle me-1"></i>Selesai
                        @else
                            <i class="fas fa-question me-1"></i>{{ ucfirst($complaint->status) }}
                        @endif
                    </span>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1" style="font-size: 0.875rem; color: var(--bs-secondary-text);">Tanggal Pengajuan</p>
                    <p class="h5 mb-0" style="color: var(--bs-text);">{{ $complaint->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Complaint Details -->
        <div class="detail-card mb-4">
            <h3 class="h5 fw-bold mb-3" style="color: var(--bs-text);">Detail Pengaduan</h3>
            <div class="mb-3">
                <p class="fw-bold mb-2" style="color: var(--bs-text);">{{ $complaint->subject }}</p>
                <p class="mb-0" style="color: var(--bs-secondary-text);">{{ $complaint->description }}</p>
            </div>
        </div>
        
        <!-- Status Timeline -->
        @if($complaint->status !== 'pending')
        <div class="detail-card mb-4">
            <h3 class="h5 fw-bold mb-3" style="color: var(--bs-text);">Timeline Proses</h3>
            <div class="space-y-3">
                <div class="timeline-step">
                    <div class="timeline-icon bg-success" style="background-color: rgba(16, 185, 129, 0.1) !important; color: #10b981;">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="fw-semibold" style="color: var(--bs-text);">Pengaduan Diterima</div>
                        <div class="text-muted" style="font-size: 0.875rem; color: var(--bs-secondary-text);">{{ $complaint->created_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
                
                @if($complaint->status === 'processing' || $complaint->status === 'completed')
                <div class="timeline-step">
                    <div class="timeline-icon bg-primary" style="background-color: rgba(59, 130, 246, 0.1) !important; color: #3b82f6;">
                        <i class="fas fa-cog text-xs"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="fw-semibold" style="color: var(--bs-text);">Sedang Diproses</div>
                        <div class="text-muted" style="font-size: 0.875rem; color: var(--bs-secondary-text);">
                            @if($complaint->updated_at)
                                {{ $complaint->updated_at->format('d M Y H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                @if($complaint->status === 'completed')
                <div class="timeline-step">
                    <div class="timeline-icon bg-success" style="background-color: rgba(16, 185, 129, 0.1) !important; color: #10b981;">
                        <i class="fas fa-check-circle text-xs"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="fw-semibold" style="color: var(--bs-text);">Selesai</div>
                        <div class="text-muted" style="font-size: 0.875rem; color: var(--bs-secondary-text);">
                            {{ $complaint->updated_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="pt-4 mt-4 border-top">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <a href="{{ route('home') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-home me-2"></i> Beranda
                </a>
                <a href="{{ route('supervision.complaint.track.form') }}" 
                   class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Lacak Lagi
                </a>
            </div>
        </div>
    </div>
</div>

@endsection