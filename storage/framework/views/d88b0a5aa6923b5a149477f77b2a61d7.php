<?php $__env->startSection('title', 'Laporan SPAK'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">📊 Laporan SPAK</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">PDF</a></li>
                <li><a class="dropdown-item" href="#">Excel</a></li>
                <li><a class="dropdown-item" href="#">CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Current Month Report Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary bg-opacity-10 border-bottom-0">
            <h5 class="card-title mb-0 text-primary">
                <i class="fas fa-chart-bar me-2"></i>Persepsi Anti Korupsi Bulan <?php echo e(now()->monthName); ?> <?php echo e(now()->year); ?>

            </h5>
        </div>
        <div class="card-body">
            <!-- Demographic Information -->
            <div class="row mb-4">
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card border-start border-success h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Umur</h6>
                            <?php $__empty_1 = true; $__currentLoopData = $currentMonthData['demographics']['age_groups'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $age => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between">
                                    <span><?php echo e($age); ?></span>
                                    <span class="badge bg-success"><?php echo e($count); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card border-start border-info h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Pendidikan</h6>
                            <?php $__empty_1 = true; $__currentLoopData = $currentMonthData['demographics']['education_levels'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $education => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between">
                                    <span><?php echo e($education); ?></span>
                                    <span class="badge bg-info"><?php echo e($count); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card border-start border-warning h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Pekerjaan</h6>
                            <?php $__empty_1 = true; $__currentLoopData = $currentMonthData['demographics']['job_types'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between">
                                    <span><?php echo e($job); ?></span>
                                    <span class="badge bg-warning"><?php echo e($count); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card border-start border-secondary h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Jenis Layanan</h6>
                            <?php $__empty_1 = true; $__currentLoopData = $currentMonthData['demographics']['service_types'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between">
                                    <span><?php echo e($service); ?></span>
                                    <span class="badge bg-secondary"><?php echo e($count); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SPAK Results -->
            <div class="row mb-4">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-primary"><?php echo e($currentMonthData['results']['total_respondents'] ?? 0); ?></h3>
                            <p class="text-muted mb-0">Responden</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-success"><?php echo e($currentMonthData['results']['percentage'] ?? 0); ?>%</h3>
                            <p class="text-muted mb-0">Persentase Indeks</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-info"><?php echo e($currentMonthData['results']['average'] ?? 0); ?></h3>
                            <p class="text-muted mb-0">Nilai Rata-rata</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SPAK Questions Results -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Pertanyaan</th>
                            <th>Rata-rata Nilai</th>
                            <th>Total Responden</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $currentMonthData['results']['scores'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($score['question']); ?></td>
                                <td><?php echo e($score['average_score']); ?></td>
                                <td><?php echo e($score['total_responses']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada data SPAK untuk bulan ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quarterly Archives Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info bg-opacity-10 border-bottom-0">
            <h5 class="card-title mb-0 text-info">
                <i class="fas fa-archive me-2"></i>Arsip Data Triwulan
            </h5>
        </div>
        <div class="card-body">
            <!-- Toggle for quarterly archive details -->
            <div class="mb-3">
                <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#quarterlyDetails" aria-expanded="false">
                    <i class="fas fa-list me-1"></i>Lihat Detail Arsip
                </button>
            </div>

            <div class="row">
                <!-- Display quarterly archives in card format -->
                <?php $__empty_1 = true; $__currentLoopData = $quarterlyArchives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $archive): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><?php echo e($archive->quarter); ?> <?php echo e($archive->year); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Responden:</small>
                                    <strong><?php echo e(($archive->calculated_values['total_respondents'] ?? 0)); ?></strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Indeks Persentase:</small>
                                    <strong><?php echo e(($archive->calculated_values['percentage'] ?? 0)); ?>%</strong>
                                </div>
                                <div>
                                    <small class="text-muted">Rata-rata:</small>
                                    <strong><?php echo e(($archive->calculated_values['average'] ?? 0)); ?></strong>
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary w-100" 
                                            type="button" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#archiveDetailModal" 
                                            onclick="loadArchiveDetails(<?php echo e($archive->id); ?>)">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-archive fa-2x mb-3"></i>
                            <p>Belum ada arsip data triwulan tersedia</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Archive Detail Modal -->
    <div class="modal fade" id="archiveDetailModal" tabindex="-1" aria-labelledby="archiveDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveDetailModalLabel">Detail Arsip Triwulan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="archive-detail-content">
                        <p class="text-muted text-center">Memuat detail arsip...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary">Cetak</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function loadArchiveDetails(archiveId) {
    fetch(`/admin/survey/archive/${archiveId}`)
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/survey/spak-report.blade.php ENDPATH**/ ?>