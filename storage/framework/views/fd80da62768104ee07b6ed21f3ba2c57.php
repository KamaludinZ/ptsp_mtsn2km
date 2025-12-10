<?php $__env->startSection('content'); ?>
<div class="dashboard-container py-4 px-3 px-lg-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                <i class="fas fa-users-cog me-2"></i>Dashboard Kepala TU
            </h1>
            <p class="text-muted mb-0">TU operations oversight dan team management</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <p class="text-muted mb-2 small">Pending Tickets</p>
                    <h2 class="mb-0 fw-bold text-warning"><?php echo e($stats['pending_tickets']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <p class="text-muted mb-2 small">In Progress</p>
                    <h2 class="mb-0 fw-bold text-info"><?php echo e($stats['in_progress_tickets']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <p class="text-muted mb-2 small">Completed Today</p>
                    <h2 class="mb-0 fw-bold text-success"><?php echo e($stats['completed_today']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <p class="text-muted mb-2 small">Overdue</p>
                    <h2 class="mb-0 fw-bold text-danger"><?php echo e($stats['overdue_tickets']); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clock text-warning me-2"></i>Pending Tickets</h5>
                </div>
                <div class="card-body">
                    <?php if($pendingTickets->count() > 0): ?>
                        <?php $__currentLoopData = $pendingTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 mb-2 border-bottom">
                                <h6 class="fw-bold"><?php echo e($ticket->ticket_number); ?></h6>
                                <p class="mb-0 small text-muted"><?php echo e($ticket->service->name ?? 'N/A'); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">No pending tickets</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Overdue Tickets</h5>
                </div>
                <div class="card-body">
                    <?php if($overdueTickets->count() > 0): ?>
                        <?php $__currentLoopData = $overdueTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 mb-2 border-bottom">
                                <h6 class="fw-bold"><?php echo e($ticket->ticket_number); ?></h6>
                                <p class="mb-0 small text-muted"><?php echo e($ticket->service->name ?? 'N/A'); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">No overdue tickets</p>
                    <?php endif; ?>
                </div>
            </div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\dashboards\kepala-tu.blade.php ENDPATH**/ ?>