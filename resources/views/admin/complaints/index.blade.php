@extends('layouts.admin')

@section('title', $whistleblowing ? 'Whistleblowing' : 'Pengaduan Masyarakat')

@php
    use App\Models\Complaint;
    $showRoute = $whistleblowing ? 'admin.whistleblowing.show' : 'admin.complaints.show';
    $indexRoute = $whistleblowing ? 'admin.whistleblowing.index' : 'admin.complaints.index';
    $tone = ['submitted' => 'secondary', 'in_review' => 'info', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'dark'];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $whistleblowing ? 'Laporan Whistleblowing' : 'Pengaduan & Saran Masyarakat' }}</h1>
            <p class="text-muted mb-0">
                {{ $whistleblowing ? 'Laporan dugaan pelanggaran. Jaga kerahasiaan identitas pelapor.' : 'Tindak lanjuti setiap laporan sampai selesai dan beri tanggapan kepada pelapor.' }}
            </p>
        </div>
        @if (! $whistleblowing && auth()->user()->hasRole('admin'))
            <a href="{{ route('admin.complaints.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2" aria-hidden="true"></i>Catat pengaduan offline</a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3" role="list" aria-label="Ringkasan status">
        <a href="{{ route($indexRoute) }}" class="btn btn-sm {{ request('status') ? 'btn-outline-secondary' : 'btn-secondary' }}" role="listitem">
            Semua <span class="badge bg-light text-dark ms-1">{{ $counts->sum() }}</span>
        </a>
        @foreach (Complaint::STATUSES as $status => $label)
            <a href="{{ route($indexRoute, ['status' => $status]) }}" class="btn btn-sm {{ request('status') === $status ? 'btn-' . $tone[$status] : 'btn-outline-' . $tone[$status] }}" role="listitem">
                {{ $label }} <span class="badge bg-light text-dark ms-1">{{ $counts->get($status, 0) }}</span>
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}" class="row g-2 mb-3">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @unless ($whistleblowing)
                    <div class="col-sm-4 col-md-3">
                        <label for="type" class="visually-hidden">Jenis</label>
                        <select id="type" name="type" class="form-select form-select-sm">
                            <option value="">Semua jenis</option>
                            <option value="complaint" @selected(request('type') === 'complaint')>Pengaduan</option>
                            <option value="suggestion" @selected(request('type') === 'suggestion')>Saran</option>
                        </select>
                    </div>
                @endunless
                <div class="col">
                    <label for="search" class="visually-hidden">Cari</label>
                    <input type="search" id="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nomor, judul, atau nama pelapor">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Nomor</th>
                            <th scope="col">Laporan</th>
                            @unless ($whistleblowing)<th scope="col">Pelapor</th>@endunless
                            <th scope="col">Status</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col">Penanggung jawab</th>
                            <th scope="col">Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($complaints as $complaint)
                            <tr>
                                <td class="text-nowrap"><a href="{{ route($showRoute, $complaint) }}" class="fw-semibold">{{ $complaint->complaint_number }}</a></td>
                                <td>
                                    <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($complaint->title, 60) }}</div>
                                    <div class="small text-muted">{{ $complaint->typeLabel() }}{{ $complaint->service ? ' · ' . $complaint->service->name : '' }}</div>
                                </td>
                                @unless ($whistleblowing)<td class="small">{{ $complaint->reporter_name ?: '–' }}</td>@endunless
                                <td><span class="badge bg-{{ $tone[$complaint->status] ?? 'secondary' }}">{{ $complaint->statusLabel() }}</span></td>
                                <td class="small">{{ Complaint::PRIORITIES[$complaint->priority] ?? '–' }}</td>
                                <td class="small">{{ $complaint->assignee?->name ?? 'Belum ada' }}</td>
                                <td class="small text-nowrap">{{ $complaint->created_at->translatedFormat('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $complaints->links() }}</div>
        </div>
    </div>
</div>
@endsection
