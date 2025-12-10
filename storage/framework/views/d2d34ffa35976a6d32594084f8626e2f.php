<?php $__env->startSection('content'); ?>
<div class="dashboard-container py-4 px-3 px-lg-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                <i class="fas fa-user-tie me-2"></i>Dashboard Kepala Sekolah
            </h1>
            <p class="text-muted mb-0">Executive overview dan approval management</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);">
                <div class="card-body">
                    <p class="mb-2 small text-white-50">PENDING APPROVALS</p>
                    <h2 class="mb-0 fw-bold"><?php echo e($stats['pending_approvals']); ?></h2>
                    <small><i class="fas fa-clock"></i> Needs your review</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body">
                    <p class="mb-2 small text-white-50">APPROVED TODAY</p>
                    <h2 class="mb-0 fw-bold"><?php echo e($stats['approved_today']); ?></h2>
                    <small><i class="fas fa-check-circle"></i> Processed today</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="card-body">
                    <p class="mb-2 small text-white-50">TICKETS THIS MONTH</p>
                    <h2 class="mb-0 fw-bold"><?php echo e($stats['total_tickets_this_month']); ?></h2>
                    <small><i class="fas fa-calendar"></i> Current month</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);">
                <div class="card-body">
                    <p class="mb-2 small text-white-50">COMPLETED</p>
                    <h2 class="mb-0 fw-bold"><?php echo e($stats['completed_this_month']); ?></h2>
                    <small><i class="fas fa-check-double"></i> This month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance & SKM -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tachometer-alt fa-3x mb-3" style="color: var(--bs-primary);"></i>
                    <h3 class="fw-bold mb-2"><?php echo e(number_format($performanceMetrics['completion_rate'], 1)); ?>%</h3>
                    <p class="text-muted mb-0">Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-smile fa-3x mb-3 text-success"></i>
                    <h3 class="fw-bold mb-2"><?php echo e(number_format($skmSummary['average_score'], 1)); ?></h3>
                    <p class="text-muted mb-0">SKM Score (<?php echo e($skmSummary['total_responses']); ?> responses)</p>
                    <span class="badge bg-success mt-2"><?php echo e($skmSummary['satisfaction_level']); ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shield-alt fa-3x mb-3 text-info"></i>
                    <h3 class="fw-bold mb-2"><?php echo e(number_format($spakSummary['average_score'], 1)); ?></h3>
                    <p class="text-muted mb-0">SPAK Score (<?php echo e($spakSummary['total_responses']); ?> responses)</p>
                    <span class="badge bg-info mt-2"><?php echo e($spakSummary['corruption_perception_index']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-check text-warning me-2"></i>Pending Approvals
            </h5>
        </div>
        <div class="card-body">
            <?php if($pendingApprovals->count() > 0): ?>
                <div class="list-group list-group-flush">
                    <?php $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="list-group-item px-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?php echo e($ticket->ticket_number); ?></h6>
                                    <p class="mb-1 text-muted small"><?php echo e($ticket->service->name ?? 'N/A'); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i><?php echo e($ticket->user->name ?? 'N/A'); ?>

                                    </small>
                                </div>
                                <div class="text-end">
                                    <a href="<?php echo e(route('backoffice.tickets.detail', $ticket->ticket_number)); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Review
                                    </a>
                                    <small class="d-block text-muted mt-2"><?php echo e($ticket->created_at->diffForHumans()); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 opacity-25 text-success"></i>
                    <p class="mb-0">No pending approvals</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .dashboard-container { width: 100%; max-width: 100%; margin: 0 auto; }
    @media (min-width: 1400px) and (max-width: 1599px) { .dashboard-container { max-width: 100%; } }
    @media (min-width: 1200px) and (max-width: 1399px) { .dashboard-container { max-width: 100%; } }
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg) !important; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\dashboards\kepala-sekolah.blade.php ENDPATH**/ ?>