<?php $__env->startSection('title', 'Daftar Laporan Whistleblowing'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Daftar Laporan Whistleblowing</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                    <li>Whistleblowing</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card bg-primary text-primary-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where('complaint_type', 'whistleblowing')->count()); ?></h4>
                        <p class="mb-0">Total Laporan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-warning text-warning-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('status', 'pending')->count()); ?></h4>
                        <p class="mb-0">Menunggu Ditinjau</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-success text-success-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('status', 'resolved')->count()); ?></h4>
                        <p class="mb-0">Terselesaikan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-info text-info-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-user-secret fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where('complaint_type', 'whistleblowing')->where('anonymous', true)->count()); ?></h4>
                        <p class="mb-0">Anonim</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Daftar Laporan Whistleblowing</h4>
            
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('admin.whistleblowing.index')); ?>" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" id="status" class="select select-bordered w-full">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="in_review" <?php echo e(request('status') == 'in_review' ? 'selected' : ''); ?>>Dalam Review</option>
                            <option value="resolved" <?php echo e(request('status') == 'resolved' ? 'selected' : ''); ?>>Terselesaikan</option>
                            <option value="closed" <?php echo e(request('status') == 'closed' ? 'selected' : ''); ?>>Ditutup</option>
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="label">
                            <span class="label-text">Prioritas</span>
                        </label>
                        <select name="priority" id="priority" class="select select-bordered w-full">
                            <option value="">Semua Prioritas</option>
                            <option value="low" <?php echo e(request('priority') == 'low' ? 'selected' : ''); ?>>Rendah</option>
                            <option value="normal" <?php echo e(request('priority') == 'normal' ? 'selected' : ''); ?>>Normal</option>
                            <option value="high" <?php echo e(request('priority') == 'high' ? 'selected' : ''); ?>>Tinggi</option>
                            <option value="urgent" <?php echo e(request('priority') == 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari laporan..." value="<?php echo e(request('search')); ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <!-- Whistleblowing Reports Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Laporan</th>
                            <th>Subjek</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Anonim</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $whistleblowingReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($report->complaint_number); ?></td>
                            <td>
                                <strong><?php echo e(Str::limit($report->subject, 50)); ?></strong><br>
                                <span class="text-sm opacity-50"><?php echo e(Str::limit($report->description, 70)); ?></span>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php if($report->status == 'pending'): ?> badge-warning
                                    <?php elseif($report->status == 'in_review'): ?> badge-info
                                    <?php elseif($report->status == 'resolved'): ?> badge-success
                                    <?php else: ?> badge-neutral <?php endif; ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $report->status))); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php if($report->priority == 'low'): ?> badge-success
                                    <?php elseif($report->priority == 'normal'): ?> badge-info
                                    <?php elseif($report->priority == 'high'): ?> badge-warning
                                    <?php else: ?> badge-error <?php endif; ?>">
                                    <?php echo e(ucfirst($report->priority)); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($report->anonymous): ?>
                                    <span class="badge badge-info">Ya</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Tidak</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($report->created_at->format('d M Y H:i')); ?></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                        <li>
                                            <a href="<?php echo e(route('admin.whistleblowing.show', $report)); ?>">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </li>
                                        <li>
                                            <form action="<?php echo e(route('admin.complaints.destroy', $report)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-error">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center py-16">
                                <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                <h5 class="text-lg font-bold text-base-content/70">Tidak ada laporan whistleblowing ditemukan.</h5>
                                <p class="text-base-content/50">Silakan sesuaikan filter pencarian Anda</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                <?php echo e($whistleblowingReports->links('vendor.pagination.daisyui')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/complaints/whistleblowing/index.blade.php ENDPATH**/ ?>