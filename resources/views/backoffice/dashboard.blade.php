@extends('layouts.app')

@section('title', 'Dashboard Back Office')

@php
    $pct = fn ($value) => $value === null ? '–' : $value . '%';
    $flow = ['submitted', 'verified', 'in_process', 'approved', 'completed'];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard Back Office</h1>
            <p class="text-muted mb-0">Antrian tugas terpadu permohonan online dan offline (Modul 7).</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('backoffice.tickets.queue') }}" class="btn btn-primary"><i class="fas fa-inbox me-2" aria-hidden="true"></i>Antrian tugas</a>
            <a href="{{ route('backoffice.tickets.search') }}" class="btn btn-outline-primary"><i class="fas fa-magnifying-glass me-2" aria-hidden="true"></i>Cari tiket</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Tiket berjalan" :value="$stats['open']" icon="fa-inbox" hint="{{ $stats['unassigned'] }} belum ditugaskan" :href="route('backoffice.tickets.queue')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Tugas saya" :value="$stats['mine']" icon="fa-list-check" tone="info" :href="route('backoffice.tickets.my')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Lewat target waktu" :value="$stats['overdue']" icon="fa-hourglass-end" :tone="$stats['overdue'] ? 'danger' : 'success'" hint="Jangka waktu standar layanan" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Menunggu persetujuan" :value="$stats['awaiting_approval']" icon="fa-clipboard-check" tone="warning" hint="Keputusan pimpinan" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Alur permohonan</h2>
                    <p class="small text-muted">Diajukan → diverifikasi TU → diproses → disetujui pimpinan → selesai.</p>
                    <div class="row row-cols-2 row-cols-md-5 g-2 text-center">
                        @foreach ($flow as $status)
                            <div class="col">
                                <div class="border rounded py-2 h-100">
                                    <div class="h4 fw-bold mb-0">{{ $stats['by_status'][$status] }}</div>
                                    <x-ticket-status :status="$status" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Ditolak: {{ $stats['by_status']['rejected'] }} · Dibatalkan: {{ $stats['by_status']['cancelled'] }} ·
                        Online {{ $stats['online'] }} / Offline {{ $stats['offline'] }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Ketepatan waktu</h2>
                    <div class="display-6 fw-bold">{{ $pct($stats['on_time_rate']) }}</div>
                    <p class="small text-muted">tiket selesai tepat waktu sesuai standar pelayanan.</p>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex justify-content-between py-1 border-bottom"><span>Rata-rata penyelesaian</span><strong>{{ \App\Support\ServiceMetrics::days($stats['avg_days']) }}</strong></li>
                        <li class="d-flex justify-content-between py-1 border-bottom"><span>Selesai bulan ini</span><strong>{{ $stats['completed_this_month'] }}</strong></li>
                        <li class="d-flex justify-content-between py-1"><span>Siap diambil di loket</span><strong>{{ $stats['ready_for_pickup'] }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Prioritas antrian (target terdekat)</h2>
                        <a href="{{ route('backoffice.tickets.queue') }}" class="small">Semua antrian</a>
                    </div>
                    @if ($queue->isEmpty())
                        <p class="text-muted mb-0">Tidak ada tiket yang sedang berjalan.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Tiket</th>
                                        <th scope="col">Layanan</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Petugas</th>
                                        <th scope="col">Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($queue as $ticket)
                                        <tr>
                                            <td>
                                                <a href="{{ route('backoffice.tickets.detail', $ticket->ticket_number) }}" class="fw-semibold">{{ $ticket->ticket_number }}</a>
                                                <div class="small text-muted">{{ $ticket->mode === 'offline' ? 'Offline' : 'Online' }} · {{ $ticket->user?->name }}</div>
                                            </td>
                                            <td class="small">{{ $ticket->service?->name }}</td>
                                            <td><x-ticket-status :status="$ticket->status" /></td>
                                            <td class="small">{{ $ticket->assignedTo?->name ?? '–' }}</td>
                                            <td class="small text-nowrap"><x-sla-due :ticket="$ticket" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Tugas saya</h2>
                        <a href="{{ route('backoffice.tickets.my') }}" class="small">Lihat semua</a>
                    </div>
                    @forelse ($myTickets as $ticket)
                        <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ route('backoffice.tickets.detail', $ticket->ticket_number) }}" class="fw-semibold text-decoration-none">{{ $ticket->ticket_number }}</a>
                                <div class="small text-muted text-truncate">{{ $ticket->service?->name }}</div>
                            </div>
                            <div class="small text-end text-nowrap"><x-sla-due :ticket="$ticket" /></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada tiket yang ditugaskan kepada Anda.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
