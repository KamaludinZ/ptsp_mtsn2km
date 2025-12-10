<?php $__env->startSection('content'); ?>
<div class="dashboard-container py-4 px-3 px-lg-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                <i class="fas fa-user-graduate me-2"></i>Dashboard Waka
            </h1>
            <p class="text-muted mb-0">Section-specific oversight dan approval management</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);">
                <div class="card-body text-center py-5">
                    <h1 class="display-3 fw-bold mb-2"><?php echo e($stats['pending_approvals']); ?></h1>
                    <p class="mb-0">Pending Approvals</p>
                    <small class="text-white-50">Requires your attention</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm hover-lift text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body text-center py-5">
                    <h1 class="display-3 fw-bold mb-2"><?php echo e($stats['approved_this_week']); ?></h1>
                    <p class="mb-0">Approved This Week</p>
                    <small class="text-white-50">Successfully processed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>Pending Approvals
            </h5>
        </div>
        <div class="card-body">
            <?php if($pendingApprovals->count() > 0): ?>
                <?php $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3 mb-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo e($ticket->ticket_number); ?></h6>
                                <p class="mb-1 text-muted small"><?php echo e($ticket->service->name ?? 'N/A'); ?></p>
                                <small class="text-muted"><?php echo e($ticket->user->name ?? 'N/A'); ?></small>
                            </div>
                            <a href="<?php echo e(route('backoffice.tickets.detail', $ticket->ticket_number)); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Review
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 opacity-25"></i>
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
    .hover-lift { transition: transform 0.2s ease; }
    .hover-lift:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg) !important; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\dashboards\waka.blade.php ENDPATH**/ ?>