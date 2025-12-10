<?php $__env->startSection('title', 'Hasil Survey'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-2">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Hasil Survey Kepuasan Masyarakat
                </h2>
                <p class="text-muted">MTsN 2 Kota Malang</p>
                <p class="text-muted small">
                    <i class="fas fa-users me-1"></i>
                    Total Responden: <strong><?php echo e($totalResponses); ?></strong>
                </p>
            </div>

            <!-- IKM Result Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Indeks Kepuasan Masyarakat (IKM)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="ikm-score-circle">
                                <svg class="score-ring" viewBox="0 0 140 140">
                                    <circle class="score-ring-circle score-ring-bg" cx="70" cy="70" r="60"></circle>
                                    <circle class="score-ring-circle score-ring-progress score-ring-success"
                                            cx="70" cy="70" r="60"
                                            style="--score: <?php echo e($ikm); ?>; --max: 100"></circle>
                                </svg>
                                <div class="score-text">
                                    <div class="score-number"><?php echo e(number_format($ikm, 2)); ?></div>
                                    <div class="score-label">Skor IKM</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4 class="fw-bold text-success mb-3"><?php echo e($ikmCategory); ?></h4>
                            <p class="text-muted mb-3">
                                Indeks Kepuasan Masyarakat (IKM) adalah data dan informasi tentang tingkat kepuasan
                                masyarakat yang diperoleh dari hasil pengukuran secara kuantitatif dan kualitatif
                                atas pendapat masyarakat dalam memperoleh pelayanan.
                            </p>

                            <!-- IKM Category Interpretation -->
                            <div class="interpretation-box p-3 bg-light rounded">
                                <h6 class="fw-semibold mb-2">
                                    <i class="fas fa-info-circle me-2"></i>Interpretasi Nilai IKM
                                </h6>
                                <ul class="mb-0 small">
                                    <li><strong>88.31 - 100:</strong> A (Sangat Baik)</li>
                                    <li><strong>76.61 - 88.30:</strong> B (Baik)</li>
                                    <li><strong>65.00 - 76.60:</strong> C (Kurang Baik)</li>
                                    <li><strong>25.00 - 64.99:</strong> D (Tidak Baik)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPAK Result Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Indeks Persepsi Anti Korupsi (IPAK)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="ikm-score-circle">
                                <svg class="score-ring" viewBox="0 0 140 140">
                                    <circle class="score-ring-circle score-ring-bg" cx="70" cy="70" r="60"></circle>
                                    <circle class="score-ring-circle score-ring-progress score-ring-warning"
                                            cx="70" cy="70" r="60"
                                            style="--score: <?php echo e($ipak); ?>; --max: 100"></circle>
                                </svg>
                                <div class="score-text">
                                    <div class="score-number"><?php echo e(number_format($ipak, 2)); ?></div>
                                    <div class="score-label">Skor IPAK</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4 class="fw-bold text-warning mb-3"><?php echo e($ipakCategory); ?></h4>
                            <p class="text-muted mb-3">
                                Indeks Persepsi Anti Korupsi (IPAK) adalah indikator untuk mengukur persepsi
                                masyarakat terhadap komitmen anti korupsi dalam penyelenggaraan pelayanan publik.
                            </p>

                            <!-- IPAK Category Interpretation -->
                            <div class="interpretation-box p-3 bg-light rounded">
                                <h6 class="fw-semibold mb-2">
                                    <i class="fas fa-info-circle me-2"></i>Interpretasi Nilai IPAK
                                </h6>
                                <ul class="mb-0 small">
                                    <li><strong>≥ 80:</strong> Sangat Baik</li>
                                    <li><strong>60 - 79:</strong> Baik</li>
                                    <li><strong>40 - 59:</strong> Cukup</li>
                                    <li><strong>< 40:</strong> Perlu Perbaikan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4 text-center">
                    <p class="mb-3 text-muted">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Hasil survey ini diperbarui secara real-time berdasarkan jawaban seluruh responden.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?php echo e(route('survey.form')); ?>" class="btn btn-primary">
                            <i class="fas fa-pen me-2"></i>Isi Survey
                        </a>
                        <a href="<?php echo e(route('home')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="text-center mt-4">
                <p class="text-muted small mb-0">
                    <i class="fas fa-calendar me-1"></i>
                    Data diperbarui: <?php echo e(now()->format('d F Y, H:i')); ?> WIB
                </p>
                <p class="text-muted small">
                    <i class="fas fa-shield-alt me-1"></i>
                    Perhitungan berdasarkan Permenpan RB No. 14 Tahun 2017
                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 3px solid rgba(255, 255, 255, 0.2);
        padding: 1.25rem 1.5rem;
    }
    .ikm-score-circle {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto;
    }
    .score-ring {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    .score-ring-circle {
        fill: none;
        stroke-width: 8;
    }
    .score-ring-bg {
        stroke: #e5e7eb;
    }
    .score-ring-progress {
        stroke-linecap: round;
        stroke-dasharray: calc(2 * 3.14159 * 60);
        stroke-dashoffset: calc(2 * 3.14159 * 60 * (1 - var(--score) / var(--max)));
        transition: stroke-dashoffset 2s ease;
    }
    .score-ring-success {
        stroke: #10b981;
    }
    .score-ring-warning {
        stroke: #fbbf24;
    }
    .score-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .score-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        color: #1f2937;
    }
    .score-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    .interpretation-box {
        border-left: 4px solid #3b82f6;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\survey\results.blade.php ENDPATH**/ ?>