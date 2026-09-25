@extends('layouts.app')

@section('title', 'Tamu Aktif')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Tamu Aktif</h1>
            <p class="text-muted mb-0">Tamu yang sudah check in dan belum check out.</p>
        </div>
        <a href="{{ route('frontdesk.triage') }}" class="btn btn-primary"><i class="fas fa-user-plus me-2" aria-hidden="true"></i>Tamu baru</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="card dash-card">
        <div class="card-body">
            @include('frontdesk.partials.visitor-table')
        </div>
    </div>
</div>
@endsection
