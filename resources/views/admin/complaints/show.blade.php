@extends('layouts.admin')

@section('title', $complaint->complaint_number)

@php
    use App\Models\Complaint;
    $isWbs = $complaint->complaint_type === 'whistleblowing';
    $indexRoute = $isWbs ? 'admin.whistleblowing.index' : 'admin.complaints.index';
    $updateRoute = $isWbs ? route('admin.whistleblowing.update-status', $complaint) : route('admin.complaints.update', $complaint);
    $evidence = json_decode($complaint->evidence_files ?? '[]', true) ?: [];
@endphp

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route($indexRoute) }}" class="small d-inline-block mb-3"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke daftar {{ $isWbs ? 'whistleblowing' : 'pengaduan' }}</a>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <h1 class="h3 fw-bold mb-0">{{ $complaint->complaint_number }}</h1>
        <span class="badge bg-secondary">{{ $complaint->typeLabel() }}</span>
        <span class="badge bg-primary">{{ $complaint->statusLabel() }}</span>
        @if ($isWbs)
            <span class="badge bg-danger"><i class="fas fa-lock me-1" aria-hidden="true"></i>Rahasia</span>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">{{ $complaint->title }}</h2>
                    <p class="text-muted small">Diterima {{ $complaint->created_at->translatedFormat('d F Y H:i') }}{{ $complaint->service ? ' · Layanan: ' . $complaint->service->name : '' }}</p>
                    <p style="white-space: pre-line;">{{ $complaint->description }}</p>

                    <dl class="row small mb-0">
                        @if ($complaint->category)
                            <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $complaint->category)) }}</dd>
                        @endif
                        @if ($complaint->incident_date)
                            <dt class="col-sm-4">Tanggal kejadian</dt><dd class="col-sm-8">{{ $complaint->incident_date->translatedFormat('d F Y') }}</dd>
                        @endif
                        @if ($complaint->incident_location)
                            <dt class="col-sm-4">Lokasi</dt><dd class="col-sm-8">{{ $complaint->incident_location }}</dd>
                        @endif
                        @if ($complaint->involved_parties)
                            <dt class="col-sm-4">Pihak terlibat</dt><dd class="col-sm-8">{{ $complaint->involved_parties }}</dd>
                        @endif
                        @if ($complaint->related_ticket_number)
                            <dt class="col-sm-4">Nomor tiket terkait</dt><dd class="col-sm-8">{{ $complaint->related_ticket_number }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Pelapor</h2>
                    @if ($complaint->anonymous)
                        <p class="mb-0 text-muted">Pelapor memilih anonim. Identitas tidak disimpan.</p>
                    @else
                        <dl class="row small mb-0">
                            <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $complaint->reporter_name ?: $complaint->complainant_name ?: '–' }}</dd>
                            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $complaint->reporter_email ?: $complaint->complainant_email ?: '–' }}</dd>
                            <dt class="col-sm-4">Telepon</dt><dd class="col-sm-8">{{ $complaint->reporter_phone ?: $complaint->complainant_contact ?: '–' }}</dd>
                        </dl>
                    @endif
                </div>
            </div>

            @if ($evidence)
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold">Bukti</h2>
                        <ul class="list-unstyled mb-0">
                            @foreach ($evidence as $index => $file)
                                <li class="py-1">
                                    <a href="{{ route('admin.complaints.evidence', [$complaint, $index]) }}" target="_blank" rel="noopener">
                                        <i class="fas fa-paperclip me-1" aria-hidden="true"></i>{{ basename($file) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Tindak lanjut</h2>
                    @if ($complaint->resolved_at)
                        <p class="small text-success">Diselesaikan {{ $complaint->resolved_at->translatedFormat('d M Y H:i') }}{{ $complaint->resolver ? ' oleh ' . $complaint->resolver->name : '' }}.</p>
                    @endif

                    @can('update', $complaint)
                        <form method="POST" action="{{ $updateRoute }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="status" class="form-label">Tahap</label>
                                <select id="status" name="status" class="form-select" required>
                                    @foreach (Complaint::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $complaint->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label for="priority" class="form-label">Prioritas</label>
                                    <select id="priority" name="priority" class="form-select" required>
                                        @foreach (Complaint::PRIORITIES as $value => $label)
                                            <option value="{{ $value }}" @selected(old('priority', $complaint->priority ?? 'normal') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="assigned_to" class="form-label">Penanggung jawab</label>
                                    <select id="assigned_to" name="assigned_to" class="form-select">
                                        <option value="">Belum ada</option>
                                        @foreach ($handlers as $handler)
                                            <option value="{{ $handler->id }}" @selected((string) old('assigned_to', $complaint->assigned_to) === (string) $handler->id)>{{ $handler->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="response" class="form-label">Tanggapan untuk pelapor</label>
                                <textarea id="response" name="response" rows="4" class="form-control" maxlength="5000" aria-describedby="response-help">{{ old('response', $complaint->response) }}</textarea>
                                <div id="response-help" class="form-text">Tampil saat pelapor melacak laporannya. Wajib diisi sebelum laporan diselesaikan.</div>
                            </div>

                            <div class="mb-3">
                                <label for="resolution_notes" class="form-label">Catatan internal</label>
                                <textarea id="resolution_notes" name="resolution_notes" rows="3" class="form-control" maxlength="5000">{{ old('resolution_notes', $complaint->resolution_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Simpan tindak lanjut</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">Anda hanya dapat melihat laporan ini.</p>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
