@extends('layouts.admin')

@section('title', 'Laporan SKM')

@section('content')
<div class="container-fluid p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-base-content">📊 Laporan SKM</h1>
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-outline btn-primary">
                <i class="fas fa-download mr-1"></i>Export
            </label>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                <li><a>PDF</a></li>
                <li><a>Excel</a></li>
                <li><a>CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Current Month Report Section -->
    <div class="card bg-base-100 shadow-xl mb-8">
        <div class="card-header bg-success/10 text-success">
            <h5 class="card-title mb-0">
                <i class="fas fa-smile mr-2"></i>Survei Kepuasan Masyarakat Bulan {{ now()->monthName }} {{ now()->year }}
            </h5>
        </div>
        <div class="card-body">
            <!-- Demographic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="card border-l-4 border-success bg-base-100 shadow-md">
                    <div class="card-body">
                        <h6 class="text-base-content/70">Umur</h6>
                        @forelse($currentMonthData['demographics']['age_groups'] ?? [] as $age => $count)
                            <div class="flex justify-between">
                                <span>{{ $age }}</span>
                                <span class="badge badge-success">{{ $count }}</span>
                            </div>
                        @empty
                            <span class="text-base-content/70">-</span>
                        @endforelse
                    </div>
                </div>
                <div class="card border-l-4 border-info bg-base-100 shadow-md">
                    <div class="card-body">
                        <h6 class="text-base-content/70">Pendidikan</h6>
                        @forelse($currentMonthData['demographics']['education_levels'] ?? [] as $education => $count)
                            <div class="flex justify-between">
                                <span>{{ $education }}</span>
                                <span class="badge badge-info">{{ $count }}</span>
                            </div>
                        @empty
                            <span class="text-base-content/70">-</span>
                        @endforelse
                    </div>
                </div>
                <div class="card border-l-4 border-warning bg-base-100 shadow-md">
                    <div class="card-body">
                        <h6 class="text-base-content/70">Pekerjaan</h6>
                        @forelse($currentMonthData['demographics']['job_types'] ?? [] as $job => $count)
                            <div class="flex justify-between">
                                <span>{{ $job }}</span>
                                <span class="badge badge-warning">{{ $count }}</span>
                            </div>
                        @empty
                            <span class="text-base-content/70">-</span>
                        @endforelse
                    </div>
                </div>
                <div class="card border-l-4 border-neutral bg-base-100 shadow-md">
                    <div class="card-body">
                        <h6 class="text-base-content/70">Jenis Layanan</h6>
                        @forelse($currentMonthData['demographics']['service_types'] ?? [] as $service => $count)
                            <div class="flex justify-between">
                                <span>{{ $service }}</span>
                                <span class="badge badge-neutral">{{ $count }}</span>
                            </div>
                        @empty
                            <span class="text-base-content/70">-</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- SKM Results -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body text-center">
                        <h3 class="text-primary text-3xl font-bold">{{ $currentMonthData['results']['total_respondents'] ?? 0 }}</h3>
                        <p class="text-base-content/70 mb-0">Responden</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body text-center">
                        <h3 class="text-success text-3xl font-bold">{{ $currentMonthData['results']['percentage'] ?? 0 }}%</h3>
                        <p class="text-base-content/70 mb-0">Persentase Indeks</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-md">
                    <div class="card-body text-center">
                        <h3 class="text-info text-3xl font-bold">{{ $currentMonthData['results']['average'] ?? 0 }}</h3>
                        <p class="text-base-content/70 mb-0">Nilai Rata-rata</p>
                    </div>
                </div>
            </div>

            <!-- SKM Questions Results -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pertanyaan</th>
                            <th>Rata-rata Nilai</th>
                            <th>Total Responden</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentMonthData['results']['scores'] ?? [] as $index => $score)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $score['question'] }}</td>
                                <td>{{ $score['average_score'] }}</td>
                                <td>{{ $score['total_responses'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/70">Tidak ada data SKM untuk bulan ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quarterly Archives Section -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-header bg-info/10 text-info">
            <h5 class="card-title mb-0">
                <i class="fas fa-archive mr-2"></i>Arsip Data Triwulan
            </h5>
        </div>
        <div class="card-body">
            <!-- Toggle for quarterly archive details -->
            <div class="mb-4">
                <button class="btn btn-info btn-sm" type="button" onclick="document.getElementById('quarterlyDetails').classList.toggle('hidden')">
                    <i class="fas fa-list mr-1"></i>Lihat Detail Arsip
                </button>
            </div>

            <div id="quarterlyDetails" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Display quarterly archives in card format -->
                    @forelse($quarterlyArchives as $archive)
                        <div class="card bg-base-100 shadow-md border">
                            <div class="card-header bg-base-200">
                                <h6 class="card-title mb-0">{{ $archive->quarter }} {{ $archive->year }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-base-content/70">Responden:</small>
                                    <strong>{{ ($archive->calculated_values['total_respondents'] ?? 0) }}</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-base-content/70">Indeks Persentase:</small>
                                    <strong>{{ ($archive->calculated_values['percentage'] ?? 0) }}%</strong>
                                </div>
                                <div>
                                    <small class="text-base-content/70">Rata-rata:</small>
                                    <strong>{{ ($archive->calculated_values['average'] ?? 0) }}</strong>
                                </div>
                                
                                <div class="mt-4">
                                    <button class="btn btn-outline btn-primary w-full" 
                                            type="button" 
                                            onclick="loadArchiveDetails({{ $archive->id }}); document.getElementById('archiveDetailModal').showModal()">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-4 text-base-content/70">
                            <i class="fas fa-archive fa-3x mb-3"></i>
                            <p>Belum ada arsip data triwulan tersedia</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Detail Modal -->
    <dialog id="archiveDetailModal" class="modal">
        <div class="modal-box w-11/12 max-w-5xl">
            <h3 class="font-bold text-lg">Detail Arsip Triwulan</h3>
            <div id="archive-detail-content" class="py-4">
                <p class="text-base-content/70 text-center">Memuat detail arsip...</p>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Tutup</button>
                    <button class="btn btn-primary">Cetak</button>
                </form>
            </div>
        </div>
    </dialog>
</div>

@push('scripts')
<script>
function loadArchiveDetails(archiveId) {
    fetch(`/suadmin/survey/archive/${archiveId}`)
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.calculated_values) {
                const results = data.calculated_values;
                
                html += '<div class="mb-4">';
                html += '<div class="row">';
                html += '<div class="col-4"><strong>Responden:</strong> ' + (results.total_respondents || 0) + '</div>';
                html += '<div class="col-4"><strong>Nilai Rata-rata:</strong> ' + (results.average || 0) + '</div>';
                html += '<div class="col-4"><strong>Indeks (%):</strong> ' + (results.percentage || 0) + '%</div>';
                html += '</div></div>';
                
                if (results.scores && results.scores.length > 0) {
                    html += '<h6>Detail Skor Pertanyaan:</h6>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>No</th><th>Pertanyaan</th><th>Nilai Rata-rata</th><th>Total Responden</th></tr></thead>';
                    html += '<tbody>';
                    
                    results.scores.forEach((score, index) => {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + score.question + '</td>';
                        html += '<td>' + score.average_score + '</td>';
                        html += '<td>' + score.total_responses + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                }
            } else {
                html = '<p class="text-muted text-center">Tidak ada data tersedia</p>';
            }
            
            document.getElementById('archive-detail-content').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading archive details:', error);
            document.getElementById('archive-detail-content').innerHTML = '<p class="text-danger text-center">Gagal memuat detail arsip</p>';
        });
}
</script>
@endpush
@endsection