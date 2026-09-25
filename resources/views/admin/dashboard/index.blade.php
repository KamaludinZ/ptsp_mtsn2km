@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard Admin</h1>
            <p class="text-muted mb-0">Kondisi data sistem dan kinerja pelayanan PTSP.</p>
        </div>
        @include('partials.period-filter')
    </div>

    <h2 class="dash-section-title">Data sistem</h2>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Pengguna" :value="number_format($system['users'])" icon="fa-users" hint="{{ $system['staff'] }} staf" :href="route('admin.users.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Layanan aktif" :value="$system['services']" icon="fa-concierge-bell" tone="info" hint="dari {{ $system['services_total'] }} layanan" :href="route('admin.services.index')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Survei aktif" :value="$system['survey_active']" icon="fa-poll" tone="success" :href="route('admin.survey.management')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Tiket baru" :value="$tickets['by_status']['submitted']" icon="fa-inbox" tone="warning" hint="Menunggu verifikasi TU" :href="route('admin.tickets.index', ['status' => 'submitted'])" />
        </div>
    </div>

    @include('partials.performance-overview')

    <div class="row g-3 mt-1">
        <div class="col-xl-6">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Tiket terbaru</h2>
                        <a href="{{ route('admin.tickets.index') }}" class="small">Semua tiket</a>
                    </div>
                    @forelse ($recentTickets as $ticket)
                        <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="fw-semibold text-decoration-none">{{ $ticket->ticket_number }}</a>
                                <div class="small text-muted text-truncate">{{ $ticket->user?->name }} · {{ $ticket->service?->name }}</div>
                            </div>
                            <div class="text-end small text-nowrap">
                                <x-ticket-status :status="$ticket->status" />
                                <div class="text-muted">{{ $ticket->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada tiket.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Pengaduan terbaru</h2>
                        <a href="{{ route('admin.complaints.index') }}" class="small">Semua pengaduan</a>
                    </div>
                    @forelse ($recentComplaints as $complaint)
                        <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route($complaint->complaint_type === 'whistleblowing' ? 'admin.whistleblowing.show' : 'admin.complaints.show', $complaint) }}" class="fw-semibold text-decoration-none">{{ $complaint->complaint_number }}</a>
                                <div class="small text-muted text-truncate">{{ $complaint->typeLabel() }} · {{ $complaint->title }}</div>
                            </div>
                            <div class="text-end small text-nowrap">
                                <span class="badge bg-secondary">{{ $complaint->statusLabel() }}</span>
                                <div class="text-muted">{{ $complaint->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada pengaduan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
