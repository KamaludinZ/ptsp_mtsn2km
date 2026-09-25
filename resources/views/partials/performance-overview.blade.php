{{--
    Service performance overview shared by the executive dashboard (Modul 13)
    and the supervision performance page. Expects $tickets, $services, $survey,
    $complaints, $visitors and $overdueTickets from ServiceMetrics.
--}}
@php
    $pct = fn ($value) => $value === null ? '–' : $value . '%';
    $modeTotal = max($tickets['online'] + $tickets['offline'], 1);
@endphp

{{-- Kinerja layanan --}}
<h2 class="dash-section-title">Kinerja Layanan</h2>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <x-stat-card label="Total permohonan" :value="number_format($tickets['total'])" icon="fa-ticket" hint="{{ $tickets['online'] }} online · {{ $tickets['offline'] }} offline" />
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card label="Sedang berjalan" :value="number_format($tickets['open'])" icon="fa-spinner" tone="warning" hint="{{ $tickets['awaiting_approval'] }} menunggu persetujuan" />
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card label="Selesai" :value="number_format($tickets['completed'])" icon="fa-circle-check" tone="success" hint="Tingkat penyelesaian {{ $pct($tickets['completion_rate']) }}" />
    </div>
    <div class="col-6 col-xl-3">
        <x-stat-card label="Melewati target waktu" :value="number_format($tickets['overdue'])" icon="fa-hourglass-end" :tone="$tickets['overdue'] ? 'danger' : 'success'" hint="Tiket berjalan lewat jangka waktu standar" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h3 class="h6 fw-bold">Ketepatan waktu (Komponen 4)</h3>
                <div class="display-6 fw-bold mb-1">{{ $pct($tickets['on_time_rate']) }}</div>
                <p class="small text-muted mb-3">tiket selesai sebelum atau tepat pada target jangka waktu standar.</p>
                @if ($tickets['on_time_rate'] !== null)
                    <div class="progress mb-3" role="progressbar" aria-label="Ketepatan waktu" aria-valuenow="{{ $tickets['on_time_rate'] }}" aria-valuemin="0" aria-valuemax="100" style="height: .5rem;">
                        <div class="progress-bar bg-{{ $tickets['on_time_rate'] >= 80 ? 'success' : ($tickets['on_time_rate'] >= 60 ? 'warning' : 'danger') }}" style="width: {{ $tickets['on_time_rate'] }}%"></div>
                    </div>
                @endif
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Rata-rata penyelesaian</span>
                    <strong>{{ \App\Support\ServiceMetrics::days($tickets['avg_days']) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h3 class="h6 fw-bold">Kanal layanan</h3>
                <p class="small text-muted">Permohonan online (portal) dibanding offline (loket PTSP).</p>
                @foreach (['online' => ['Online', 'primary'], 'offline' => ['Offline (loket)', 'info']] as $mode => [$label, $tone])
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $label }}</span>
                        <strong>{{ $tickets[$mode] }} ({{ round($tickets[$mode] / $modeTotal * 100) }}%)</strong>
                    </div>
                    <div class="progress mb-3" role="progressbar" aria-label="{{ $label }}" aria-valuenow="{{ round($tickets[$mode] / $modeTotal * 100) }}" aria-valuemin="0" aria-valuemax="100" style="height: .5rem;">
                        <div class="progress-bar bg-{{ $tone }}" style="width: {{ $tickets[$mode] / $modeTotal * 100 }}%"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h3 class="h6 fw-bold">Status permohonan</h3>
                <ul class="list-unstyled mb-0">
                    @foreach ($tickets['by_status'] as $status => $count)
                        <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <x-ticket-status :status="$status" />
                            <strong>{{ $count }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- SKM, SPAK, pengaduan, buku tamu --}}
<h2 class="dash-section-title">Kepuasan, Integritas &amp; Pengaduan</h2>
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
<p class="small text-muted mb-4">
    {{ number_format($survey['respondents']) }} responden survei pada periode ini.
    Buku tamu: {{ $visitors['today'] }} tamu hari ini ({{ $visitors['active'] }} masih di lokasi), {{ $visitors['month'] }} tamu bulan ini.
</p>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h2 class="h6 fw-bold">Kinerja per layanan</h2>
                @if (count($services))
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Layanan</th>
                                    <th scope="col">Standar</th>
                                    <th scope="col" class="text-end">Masuk</th>
                                    <th scope="col" class="text-end">Selesai</th>
                                    <th scope="col" class="text-end">Rata-rata</th>
                                    <th scope="col" class="text-end">Terlambat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($services as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td class="small text-muted">{{ $row['processing_time'] ?: '–' }}</td>
                                        <td class="text-end">{{ $row['total'] }}</td>
                                        <td class="text-end">{{ $row['completed'] }}</td>
                                        <td class="text-end">{{ \App\Support\ServiceMetrics::days($row['avg_days']) }}</td>
                                        <td class="text-end {{ $row['overdue'] ? 'text-danger fw-semibold' : '' }}">{{ $row['overdue'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada permohonan pada periode ini.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dash-card h-100">
            <div class="card-body">
                <h2 class="h6 fw-bold">Perlu perhatian: lewat target</h2>
                @forelse ($overdueTickets as $ticket)
                    <div class="d-flex justify-content-between gap-2 py-2 border-bottom">
                        <div class="min-w-0">
                            @can('backoffice.access')
                                <a href="{{ route('backoffice.tickets.detail', $ticket->ticket_number) }}" class="fw-semibold text-decoration-none">{{ $ticket->ticket_number }}</a>
                            @else
                                <span class="fw-semibold">{{ $ticket->ticket_number }}</span>
                            @endcan
                            <div class="small text-muted text-truncate">{{ $ticket->service?->name }}</div>
                        </div>
                        <div class="small text-end text-nowrap"><x-sla-due :ticket="$ticket" /></div>
                    </div>
                @empty
                    <p class="text-muted mb-0"><i class="fas fa-circle-check text-success me-1" aria-hidden="true"></i>Tidak ada tiket yang melewati target waktu.</p>
                @endforelse
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @can('backoffice.access')
                        <a href="{{ route('backoffice.reports') }}" class="btn btn-sm btn-outline-primary">Laporan kinerja</a>
                    @endcan
                    <a href="{{ route('admin.performance.report') }}" class="btn btn-sm btn-outline-secondary">Laporan SKM/SPAK</a>
                </div>
            </div>
        </div>
    </div>
</div>
