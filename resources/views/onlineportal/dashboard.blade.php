@extends('layouts.app')

@section('title', 'Dashboard Saya')

@php
    $inProgress = $stats['by_status']['verified'] + $stats['by_status']['in_process'] + $stats['by_status']['approved'];
    $steps = ['submitted' => 'Diajukan', 'verified' => 'Diverifikasi', 'in_process' => 'Diproses', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Halo, {{ $user->name }}</h1>
            <p class="text-muted mb-0">Pantau permohonan layanan Anda dan ambil hasilnya di sini.</p>
        </div>
        <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary"><i class="fas fa-file-circle-plus me-2" aria-hidden="true"></i>Ajukan layanan</a>
    </div>

    @if ($unrated > 0)
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2" role="status">
            <span><i class="fas fa-star-half-stroke me-2" aria-hidden="true"></i>{{ $unrated }} layanan Anda sudah selesai. Bantu kami meningkatkan pelayanan dengan mengisi survei kepuasan.</span>
            <a href="{{ route('survey.form') }}" class="btn btn-sm btn-info">Isi survei</a>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Total permohonan" :value="$stats['total']" icon="fa-ticket" :href="route('onlineportal.my-tickets')" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Menunggu verifikasi" :value="$stats['by_status']['submitted']" icon="fa-inbox" tone="secondary" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Sedang diproses" :value="$inProgress" icon="fa-spinner" tone="warning" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Selesai" :value="$stats['completed']" icon="fa-circle-check" tone="success" :hint="$stats['by_status']['rejected'] ? $stats['by_status']['rejected'] . ' ditolak' : null" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Permohonan terbaru</h2>
                        <a href="{{ route('onlineportal.my-tickets') }}" class="small">Semua tiket</a>
                    </div>
                    @forelse ($tickets as $ticket)
                        @php $current = array_search($ticket->status, array_keys($steps), true); @endphp
                        <div class="py-3 border-bottom">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div class="min-w-0">
                                    <a href="{{ route('onlineportal.ticket.detail', $ticket->ticket_number) }}" class="fw-semibold text-decoration-none">{{ $ticket->ticket_number }}</a>
                                    <div class="small text-muted">{{ $ticket->service?->name }} · diajukan {{ $ticket->created_at->translatedFormat('d M Y') }}</div>
                                </div>
                                <div class="text-end">
                                    <x-ticket-status :status="$ticket->status" />
                                    @if ($current !== false && $ticket->status !== 'completed')
                                        <div class="small text-muted mt-1">Perkiraan selesai: <x-sla-due :ticket="$ticket" /></div>
                                    @endif
                                </div>
                            </div>
                            @if ($current !== false)
                                <div class="progress mt-2" style="height: .35rem;" role="progressbar" aria-label="Tahap {{ $steps[$ticket->status] }}" aria-valuenow="{{ $current + 1 }}" aria-valuemin="1" aria-valuemax="{{ count($steps) }}">
                                    <div class="progress-bar bg-{{ $ticket->status === 'completed' ? 'success' : 'primary' }}" style="width: {{ ($current + 1) / count($steps) * 100 }}%"></div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted">Anda belum mengajukan layanan.</p>
                            <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-outline-primary btn-sm">Lihat katalog layanan</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-card mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Hasil layanan</h2>
                    @forelse ($results as $ticket)
                        <div class="py-2 border-bottom">
                            <div class="fw-semibold small">{{ $ticket->service?->name }}</div>
                            <div class="small text-muted mb-1">{{ $ticket->ticket_number }}</div>
                            @if ($ticket->output)
                                <a href="{{ route('onlineportal.ticket.download', $ticket->ticket_number) }}" class="btn btn-sm btn-success"><i class="fas fa-download me-1" aria-hidden="true"></i>Unduh dokumen</a>
                            @endif
                            @if ($ticket->ready_for_pickup)
                                <div class="small text-success"><i class="fas fa-box-open me-1" aria-hidden="true"></i>Siap diambil di Loket PTSP</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada hasil layanan yang siap.</p>
                    @endforelse
                </div>
            </div>
            <div class="card dash-card">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Bantuan</h2>
                    <ul class="list-unstyled small mb-0">
                        <li class="py-1"><a href="{{ route('onlineportal.track.ticket.form') }}"><i class="fas fa-search me-2" aria-hidden="true"></i>Lacak tiket dengan nomor</a></li>
                        <li class="py-1"><a href="{{ route('supervision.complaint.submit') }}"><i class="fas fa-comments me-2" aria-hidden="true"></i>Sampaikan pengaduan atau saran</a></li>
                        <li class="py-1"><a href="{{ route('supervision.complaint.track.form') }}"><i class="fas fa-magnifying-glass me-2" aria-hidden="true"></i>Lacak pengaduan</a></li>
                        <li class="py-1"><a href="{{ route('profile.edit') }}"><i class="fas fa-user-cog me-2" aria-hidden="true"></i>Perbarui profil</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
