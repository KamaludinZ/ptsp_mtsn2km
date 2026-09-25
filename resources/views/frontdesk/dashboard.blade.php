@extends('layouts.app')

@section('title', 'Dashboard Loket')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard Loket PTSP</h1>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l, d F Y') }} · tamu, registrasi layanan offline, dan produk yang siap diambil.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('frontdesk.triage') }}" class="btn btn-primary"><i class="fas fa-user-check me-2" aria-hidden="true"></i>Triage pengunjung</a>
            <a href="{{ route('frontdesk.service.application') }}" class="btn btn-outline-primary"><i class="fas fa-file-circle-plus me-2" aria-hidden="true"></i>Registrasi layanan</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <h2 class="dash-section-title">Buku tamu</h2>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <x-stat-card label="Tamu hari ini" :value="$stats['today']" icon="fa-users" :href="route('frontdesk.visitor-book')" />
        </div>
        <div class="col-6 col-lg-4">
            <x-stat-card label="Masih di lokasi" :value="$stats['active']" icon="fa-door-open" tone="warning" hint="Belum check out" :href="route('frontdesk.active-visitors')" />
        </div>
        <div class="col-12 col-lg-4">
            <x-stat-card label="Tamu bulan ini" :value="$stats['month']" icon="fa-calendar-days" tone="info" />
        </div>
    </div>

    <h2 class="dash-section-title">Layanan offline (loket)</h2>
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl">
            <x-stat-card label="Registrasi hari ini" :value="$stats['offline_today']" icon="fa-file-circle-plus" />
        </div>
        <div class="col-6 col-xl">
            <x-stat-card label="Menunggu verifikasi TU" :value="$stats['awaiting_verification']" icon="fa-inbox" tone="secondary" />
        </div>
        <div class="col-6 col-xl">
            <x-stat-card label="Sedang diproses" :value="$stats['in_progress']" icon="fa-spinner" tone="warning" />
        </div>
        <div class="col-6 col-xl">
            <x-stat-card label="Siap diambil" :value="$stats['ready_for_pickup']" icon="fa-box-open" tone="success" hint="Serahkan di loket" />
        </div>
        <div class="col-12 col-xl">
            <x-stat-card label="Lewat target waktu" :value="$stats['overdue']" icon="fa-hourglass-end" :tone="$stats['overdue'] ? 'danger' : 'success'" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Produk siap diambil</h2>
                    @forelse ($pickupTickets as $ticket)
                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="fw-semibold">{{ $ticket->ticket_number }}</span>
                                <span class="small text-muted text-nowrap">{{ $ticket->actual_completion_date?->translatedFormat('d M') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div class="small text-muted text-truncate">{{ $ticket->user?->name }} · {{ $ticket->service?->name }}</div>
                                <form method="POST" action="{{ route('frontdesk.tickets.hand-over', $ticket) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Serahkan</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Tidak ada produk yang menunggu diambil.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Registrasi offline hari ini</h2>
                    @forelse ($recentTickets as $ticket)
                        <div class="py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <a href="{{ route('frontdesk.service.success', $ticket->ticket_number) }}" class="fw-semibold text-decoration-none">{{ $ticket->ticket_number }}</a>
                                <x-ticket-status :status="$ticket->status" />
                            </div>
                            <div class="small text-muted text-truncate">{{ $ticket->user?->name }} · {{ $ticket->service?->name }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada registrasi layanan hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Tamu terbaru</h2>
                        <a href="{{ route('frontdesk.visitor-book') }}" class="small">Buku tamu</a>
                    </div>
                    @forelse ($recentVisitors as $visitor)
                        <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $visitor->name }}</div>
                                <div class="small text-muted text-truncate">{{ $visitor->purpose }}</div>
                            </div>
                            <div class="small text-end text-nowrap">
                                {{ $visitor->check_in_time?->format('H:i') }}
                                <div>
                                    @if ($visitor->check_out_time)
                                        <span class="text-muted">Keluar {{ $visitor->check_out_time->format('H:i') }}</span>
                                    @else
                                        <span class="text-success">Di lokasi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada tamu hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
