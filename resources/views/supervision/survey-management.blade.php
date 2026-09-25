@extends('layouts.app')

@section('title', 'Dashboard Pengawasan')

@php
    $pct = fn ($value) => $value === null ? '–' : $value . '%';
    $typeLabels = ['complaint' => 'Pengaduan', 'suggestion' => 'Saran', 'whistleblowing' => 'Whistleblowing'];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Dashboard Pengawasan</h1>
        <p class="text-muted mb-0">Indeks kepuasan (SKM), persepsi anti korupsi (SPAK), dan tindak lanjut pengaduan tahun {{ now()->year }}.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <x-stat-card label="IKM (Survei Kepuasan)" :value="$survey['ikm'] !== null ? number_format($survey['ikm'], 2, ',', '.') : '–'" icon="fa-face-smile" tone="success" :hint="$survey['ikm_grade'] ?? 'Belum ada responden'" :href="route('admin.skm.report')" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card label="IPAK (Persepsi Anti Korupsi)" :value="$survey['ipak'] !== null ? number_format($survey['ipak'], 2, ',', '.') : '–'" icon="fa-scale-balanced" tone="info" :hint="$survey['ipak_grade'] ?? 'Belum ada responden'" :href="route('admin.spak.report')" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card label="Pengaduan & saran" :value="$complaints['dumas']['total']" icon="fa-comments" tone="warning" hint="{{ $complaints['dumas']['new'] }} baru · {{ $complaints['dumas']['in_progress'] }} ditangani · {{ $pct($complaints['dumas']['resolution_rate']) }} selesai" :href="route('admin.complaints.index')" />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card label="Whistleblowing" :value="$complaints['whistleblowing']['total']" icon="fa-user-secret" tone="danger" hint="{{ $complaints['whistleblowing']['new'] }} baru · {{ $pct($complaints['whistleblowing']['resolution_rate']) }} selesai" :href="route('admin.whistleblowing.index')" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Progres tindak lanjut pengaduan</h2>
                    @foreach (['dumas' => 'Pengaduan & saran', 'whistleblowing' => 'Whistleblowing'] as $key => $label)
                        @php $row = $complaints[$key]; $total = max($row['total'], 1); @endphp
                        <div class="d-flex justify-content-between small mt-3 mb-1">
                            <span class="fw-semibold">{{ $label }}</span>
                            <span>{{ $row['resolved'] }} dari {{ $row['total'] }} selesai</span>
                        </div>
                        <div class="progress" style="height: .6rem;" role="img" aria-label="{{ $label }}: {{ $row['new'] }} baru, {{ $row['in_progress'] }} ditangani, {{ $row['resolved'] }} selesai">
                            <div class="progress-bar bg-success" style="width: {{ $row['resolved'] / $total * 100 }}%"></div>
                            <div class="progress-bar bg-warning" style="width: {{ $row['in_progress'] / $total * 100 }}%"></div>
                            <div class="progress-bar bg-secondary" style="width: {{ $row['new'] / $total * 100 }}%"></div>
                        </div>
                    @endforeach
                    <div class="d-flex flex-wrap gap-3 small text-muted mt-3">
                        <span><i class="fas fa-square text-success me-1" aria-hidden="true"></i>Selesai</span>
                        <span><i class="fas fa-square text-warning me-1" aria-hidden="true"></i>Ditangani</span>
                        <span><i class="fas fa-square text-secondary me-1" aria-hidden="true"></i>Baru</span>
                    </div>
                    <p class="small text-muted mt-3 mb-0">{{ number_format($survey['respondents']) }} responden survei tahun ini.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card dash-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Laporan baru, belum ditindaklanjuti</h2>
                        <a href="{{ route('admin.complaints.index') }}" class="small">Semua pengaduan</a>
                    </div>
                    @forelse ($newComplaints as $complaint)
                        @php $isWbs = $complaint->complaint_type === 'whistleblowing'; @endphp
                        <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                            <div class="min-w-0">
                                <a href="{{ $isWbs ? route('admin.whistleblowing.show', $complaint) : route('admin.complaints.show', $complaint) }}" class="fw-semibold text-decoration-none">{{ $complaint->complaint_number }}</a>
                                <div class="small text-muted text-truncate">{{ $complaint->title }}</div>
                            </div>
                            <div class="text-end small text-nowrap">
                                <span class="badge bg-{{ $isWbs ? 'danger' : 'warning' }} bg-opacity-10 text-{{ $isWbs ? 'danger' : 'warning' }}-emphasis">{{ $typeLabels[$complaint->complaint_type] ?? $complaint->complaint_type }}</span>
                                <div class="text-muted">{{ $complaint->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0"><i class="fas fa-circle-check text-success me-1" aria-hidden="true"></i>Semua laporan sudah ditindaklanjuti.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card dash-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h2 class="h6 fw-bold mb-0">Edisi survei</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('supervision.performance') }}" class="btn btn-sm btn-outline-primary">Kinerja pelayanan</a>
                    <a href="{{ route('admin.performance.report') }}" class="btn btn-sm btn-outline-secondary">Laporan SKM/SPAK</a>
                </div>
            </div>
            @if ($surveys->isEmpty())
                <p class="text-muted mb-0">Belum ada survei.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Periode</th>
                                <th scope="col" class="text-end">Soal</th>
                                <th scope="col" class="text-end">Responden</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="visually-hidden">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($surveys as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ strtoupper($item->type) }}</td>
                                    <td class="small">{{ $item->start_date?->translatedFormat('d M Y') ?? '–' }} – {{ $item->end_date?->translatedFormat('d M Y') ?? 'berjalan' }}</td>
                                    <td class="text-end">{{ $item->questions_count }}</td>
                                    <td class="text-end">{{ $item->responses_count }}</td>
                                    <td><span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td><a href="{{ route('supervision.survey.results', $item->id) }}">Lihat hasil</a></td>
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
