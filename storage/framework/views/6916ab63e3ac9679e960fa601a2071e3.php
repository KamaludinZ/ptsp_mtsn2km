<?php $__env->startSection('content'); ?>
<div class="dashboard-container py-4 px-3 px-lg-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                        <i class="fas fa-user-shield me-2"></i>Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">Kelola layanan dan tiket sistem PTSP MTsN 2 Kota Malang</p>
                </div>
                <div>
                    <a href="<?php echo e(route('admin.services.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Layanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Total Services -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Total Services</p>
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['total_services']); ?></h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> <?php echo e($stats['active_services']); ?> active
                            </small>
                        </div>
                        <div class="icon-box rounded-3 p-3" style="background: rgba(147, 51, 234, 0.1);">
                            <i class="fas fa-concierge-bell fa-2x" style="color: #9333ea;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Total Tickets</p>
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['total_tickets']); ?></h3>
                            <small class="text-muted">
                                <i class="fas fa-chart-line"></i> All time
                            </small>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-ticket-alt fa-2x" style="color: var(--bs-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Tickets -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Pending</p>
                            <h3 class="mb-0 fw-bold text-warning"><?php echo e($stats['pending_tickets']); ?></h3>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Awaiting action
                            </small>
                        </div>
                        <div class="icon-box rounded-3 p-3" style="background: rgba(245, 158, 11, 0.1);">
                            <i class="fas fa-hourglass-half fa-2x" style="color: #f59e0b;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">In Progress</p>
                            <h3 class="mb-0 fw-bold text-info"><?php echo e($stats['in_progress_tickets']); ?></h3>
                            <small class="text-muted">
                                <i class="fas fa-spinner"></i> Being processed
                            </small>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-sync fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-white-50 small fw-semibold">COMPLETED</p>
                            <h2 class="mb-0 fw-bold"><?php echo e($stats['completed_tickets']); ?></h2>
                            <small class="text-white-75">Successfully completed</small>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-chart-pie text-primary me-2"></i>Statistik Tiket
                    </h5>
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <div class="mb-2">
                                <i class="fas fa-hourglass-half fa-2x" style="color: #f59e0b;"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($stats['pending_tickets']); ?></h4>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div>
                            <div class="mb-2">
                                <i class="fas fa-sync fa-2x text-info"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($stats['in_progress_tickets']); ?></h4>
                            <small class="text-muted">Processing</small>
                        </div>
                        <div>
                            <div class="mb-2">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <h4 class="mb-1"><?php echo e($stats['completed_tickets']); ?></h4>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="<?php echo e(route('admin.services.index')); ?>" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(147, 51, 234, 0.05);">
                            <i class="fas fa-concierge-bell fa-2x mb-2" style="color: #9333ea;"></i>
                            <small class="fw-semibold d-block">Manage Services</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="<?php echo e(route('admin.service-categories.index')); ?>" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(59, 130, 246, 0.05);">
                            <i class="fas fa-tags fa-2x mb-2" style="color: #3b82f6;"></i>
                            <small class="fw-semibold d-block">Categories</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="<?php echo e(route('backoffice.tickets.all')); ?>" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(234, 88, 12, 0.05);">
                            <i class="fas fa-ticket-alt fa-2x mb-2" style="color: var(--bs-secondary);"></i>
                            <small class="fw-semibold d-block">View Tickets</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="<?php echo e(route('backoffice.reports')); ?>" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(16, 185, 129, 0.05);">
                            <i class="fas fa-chart-bar fa-2x mb-2" style="color: #10b981;"></i>
                            <small class="fw-semibold d-block">Reports</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-history text-primary me-2"></i>Recent Tickets
            </h5>
            <a href="<?php echo e(route('backoffice.tickets.all')); ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-right me-1"></i>View All
            </a>
        </div>
        <div class="card-body">
            <?php if($recentTickets->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Ticket Number</th>
                                <th>Service</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $recentTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?php echo e($ticket->ticket_number); ?></span>
                                    </td>
                                    <td><?php echo e($ticket->service->name ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle-sm bg-primary bg-opacity-10 me-2">
                                                <span class="small fw-bold" style="color: var(--bs-primary);"><?php echo e(strtoupper(substr($ticket->user->name ?? 'U', 0, 1))); ?></span>
                                            </div>
                                            <span><?php echo e($ticket->user->name ?? 'N/A'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge
                                            <?php if($ticket->status === 'completed'): ?> bg-success-subtle text-success
                                            <?php elseif($ticket->status === 'in_progress'): ?> bg-info-subtle text-info
                                            <?php else: ?> bg-warning-subtle text-warning
                                            <?php endif; ?> px-3 py-2">
                                            <?php echo e(ucfirst($ticket->status)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e($ticket->created_at->diffForHumans()); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">No recent tickets</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .dashboard-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    @media (min-width: 1800px) {
        .dashboard-container {
            max-width: 100%;
            margin: 0 auto;
        }
    }

    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg) !important;
    }

    .quick-action-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .avatar-circle-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bg-success-subtle {
        background-color: rgba(16, 185, 129, 0.1) !important;
    }
    .bg-warning-subtle {
        background-color: rgba(245, 158, 11, 0.1) !important;
    }
    .bg-danger-subtle {
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    .bg-info-subtle {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\dashboards\admin.blade.php ENDPATH**/ ?>