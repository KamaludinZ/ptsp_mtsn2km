@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard Eksekutif</h1>
            <p class="text-muted mb-0">Rekap kinerja pelayanan PTSP, kepuasan masyarakat, dan pengaduan.</p>
        </div>
        @include('partials.period-filter')
    </div>

    @if ($myApprovals > 0)
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2" role="status">
            <span><i class="fas fa-clipboard-check me-2" aria-hidden="true"></i><strong>{{ $myApprovals }}</strong> permohonan menunggu persetujuan Anda.</span>
            <a href="{{ route('leadership.approvals') }}" class="btn btn-sm btn-warning">Buka daftar persetujuan</a>
        </div>
    @endif

    @include('partials.performance-overview')
</div>
@endsection
