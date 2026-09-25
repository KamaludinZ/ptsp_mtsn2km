@extends('layouts.app')

@section('title', 'Persetujuan Layanan')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Persetujuan Layanan</h1>
        <p class="text-muted mb-0">Permohonan yang berkasnya sudah diverifikasi petugas TU dan menunggu keputusan Anda.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <h2 class="dash-section-title">Menunggu keputusan ({{ $pending->count() }})</h2>

    @forelse ($pending as $ticket)
        <article class="card dash-card mb-3" aria-labelledby="ticket-{{ $ticket->id }}">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h3 id="ticket-{{ $ticket->id }}" class="h6 fw-bold mb-0">{{ $ticket->ticket_number }}</h3>
                            <x-ticket-status :status="$ticket->status" />
                            <span class="badge bg-light text-dark border">{{ $ticket->mode === 'offline' ? 'Offline (loket)' : 'Online' }}</span>
                        </div>
                        <div class="fw-semibold">{{ $ticket->service?->name }}</div>
                        <div class="small text-muted mb-2">
                            {{ $ticket->user?->name }} · diajukan {{ $ticket->created_at->translatedFormat('d M Y') }} · target selesai <x-sla-due :ticket="$ticket" />
                        </div>
                        @if ($ticket->notes)
                            <p class="small mb-2" style="white-space: pre-line;">{{ \Illuminate\Support\Str::limit($ticket->notes, 300) }}</p>
                        @endif
                        <a href="{{ route('backoffice.tickets.detail', $ticket->ticket_number) }}" class="small">Lihat detail &amp; berkas persyaratan <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></a>
                    </div>
                    <div class="col-lg-5">
                        <form method="POST" action="{{ route('leadership.approvals.decide', $ticket) }}" class="border rounded p-3 bg-light">
                            @csrf
                            <fieldset class="mb-2">
                                <legend class="small fw-semibold mb-1">Tanda tangan produk layanan</legend>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="signature_type" id="tte-{{ $ticket->id }}" value="tte" @checked($ticket->service?->is_digital_product)>
                                    <label class="form-check-label" for="tte-{{ $ticket->id }}">TTE (elektronik)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="signature_type" id="ttd-{{ $ticket->id }}" value="ttd" @checked(! $ticket->service?->is_digital_product)>
                                    <label class="form-check-label" for="ttd-{{ $ticket->id }}">TTD (basah)</label>
                                </div>
                            </fieldset>
                            <label for="notes-{{ $ticket->id }}" class="form-label small fw-semibold">Catatan <span class="fw-normal text-muted">(wajib bila ditolak)</span></label>
                            <textarea id="notes-{{ $ticket->id }}" name="notes" rows="2" maxlength="500" class="form-control form-control-sm mb-2"></textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm flex-fill"><i class="fas fa-check me-1" aria-hidden="true"></i>Setujui</button>
                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm flex-fill"><i class="fas fa-xmark me-1" aria-hidden="true"></i>Tolak</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card dash-card mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-check fa-2x text-success mb-3" aria-hidden="true"></i>
                <p class="mb-0">Tidak ada permohonan yang menunggu persetujuan Anda.</p>
            </div>
        </div>
    @endforelse

    <h2 class="dash-section-title mt-4">Riwayat keputusan Anda</h2>
    <div class="card dash-card">
        <div class="card-body">
            @if ($history->isEmpty())
                <p class="text-muted mb-0">Belum ada keputusan.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Tiket</th>
                                <th scope="col">Layanan</th>
                                <th scope="col">Keputusan</th>
                                <th scope="col">Tanda tangan</th>
                                <th scope="col">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $ticket)
                                <tr>
                                    <td><a href="{{ route('backoffice.tickets.detail', $ticket->ticket_number) }}">{{ $ticket->ticket_number }}</a></td>
                                    <td>{{ $ticket->service?->name }}</td>
                                    <td>
                                        @if ($ticket->approval_status === 'approved')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->signature_type ? strtoupper($ticket->signature_type) : '–' }}</td>
                                    <td class="small text-muted">{{ $ticket->approved_at?->translatedFormat('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
