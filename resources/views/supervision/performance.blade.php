@extends('layouts.app')

@section('title', 'Kinerja Pelayanan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kinerja Pelayanan</h1>
            <p class="text-muted mb-0">Pengawasan internal (Komponen 9): waktu penyelesaian dibanding standar, kanal layanan, SKM/SPAK, dan pengaduan.</p>
        </div>
        @include('partials.period-filter')
    </div>

    @include('partials.performance-overview')
</div>
@endsection
