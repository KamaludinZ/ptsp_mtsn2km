@extends('layouts.app')

@section('title', 'Buku Tamu')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Buku Tamu</h1>
            <p class="text-muted mb-0">Catatan kunjungan tanggal {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d F Y') }} ({{ $visitors->total() }} tamu).</p>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label for="date" class="small text-muted">Tanggal</label>
            <input type="date" id="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="form-control form-control-sm">
            <button class="btn btn-sm btn-outline-primary">Tampilkan</button>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="card dash-card">
        <div class="card-body">
            @include('frontdesk.partials.visitor-table')
            <div class="mt-3">{{ $visitors->links() }}</div>
        </div>
    </div>
</div>
@endsection
