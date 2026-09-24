<div class="dashboard-container py-4 px-3 px-lg-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);">
                <div class="card-body py-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">Selamat Datang, <?php echo e($user->name); ?>! 👋</h2>
                            <p class="mb-0 text-white-75">Kelola tiket layanan Anda di sini</p>
                        </div>
                        <div class="d-none d-md-block">
                            <i class="fas fa-user-circle fa-4x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body text-center py-4">
                    <div class="icon-box bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="fas fa-ticket-alt fa-2x" style="color: var(--bs-primary);"></i>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo e($stats['total']); ?></h3>
                    <p class="text-muted mb-0 small">Total Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body text-center py-4">
                    <div class="icon-box rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-clock fa-2x" style="color: #f59e0b;"></i>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo e($stats['pending']); ?></h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body text-center py-4">
                    <div class="icon-box bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="fas fa-spinner fa-2x text-info"></i>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo e($stats['in_progress']); ?></h3>
                    <p class="text-muted mb-0 small">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body text-center py-4">
                    <div class="icon-box bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo e($stats['completed']); ?></h3>
                    <p class="text-muted mb-0 small">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body text-center py-4">
                        <div class="icon-box rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(147, 51, 234, 0.1);">
                            <i class="fas fa-plus-circle fa-2x" style="color: #9333ea;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Ajukan Layanan</h6>
                        <p class="text-muted small mb-0">Buat permohonan layanan baru</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a href="<?php echo e(route('onlineportal.my-tickets')); ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body text-center py-4">
                        <div class="icon-box rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(59, 130, 246, 0.1);">
                            <i class="fas fa-list fa-2x" style="color: #3b82f6;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Tiket Saya</h6>
                        <p class="text-muted small mb-0">Lihat semua tiket Anda</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a href="<?php echo e(route('onlineportal.track.ticket.form')); ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body text-center py-4">
                        <div class="icon-box rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(234, 88, 12, 0.1);">
                            <i class="fas fa-search fa-2x" style="color: var(--bs-secondary);"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Lacak Tiket</h6>
                        <p class="text-muted small mb-0">Cek status permohonan</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <a href="<?php echo e(route('profile.edit')); ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body text-center py-4">
                        <div class="icon-box rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(16, 185, 129, 0.1);">
                            <i class="fas fa-user-cog fa-2x" style="color: #10b981;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Profil Saya</h6>
                        <p class="text-muted small mb-0">Edit informasi akun</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-history text-primary me-2"></i>Tiket Terbaru
            </h5>
            <a href="<?php echo e(route('onlineportal.my-tickets')); ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-arrow-right me-1"></i>Lihat Semua
            </a>
        </div>
        <div class="card-body">
            <?php if($tickets->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Tiket</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo e($ticket->ticket_number); ?></span>
                                    </td>
                                    <td><?php echo e($ticket->service->name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge px-3 py-2
                                            <?php if($ticket->status === 'completed'): ?> bg-success-subtle text-success
                                            <?php elseif($ticket->status === 'in_progress'): ?> bg-info-subtle text-info
                                            <?php elseif($ticket->status === 'pending'): ?> bg-warning-subtle text-warning
                                            <?php else: ?> bg-secondary-subtle text-secondary
                                            <?php endif; ?>">
                                            <?php if($ticket->status === 'completed'): ?> <i class="fas fa-check-circle me-1"></i>Selesai
                                            <?php elseif($ticket->status === 'in_progress'): ?> <i class="fas fa-spinner me-1"></i>Diproses
                                            <?php elseif($ticket->status === 'pending'): ?> <i class="fas fa-clock me-1"></i>Pending
                                            <?php else: ?> <?php echo e(ucfirst($ticket->status)); ?>

                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e($ticket->created_at->format('d M Y, H:i')); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('onlineportal.ticket.detail', $ticket->ticket_number)); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="icon-box bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-ticket-alt fa-3x opacity-25" style="color: var(--bs-primary);"></i>
                    </div>
                    <h5 class="text-muted mb-3">Belum Ada Tiket</h5>
                    <p class="text-muted mb-4">Anda belum memiliki permohonan layanan</p>
                    <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajukan Layanan Sekarang
                    </a>
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
            max-width: 1600px;
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

    .bg-success-subtle { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1) !important; }
    .bg-info-subtle { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(107, 114, 128, 0.1) !important; }

    .text-white-75 { color: rgba(255, 255, 255, 0.75) !important; }
</style>
<?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/onlineportal/dashboard-content.blade.php ENDPATH**/ ?>