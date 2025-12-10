<?php $__env->startSection('title', 'Daftar Pengaduan'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Daftar Pengaduan</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="<?php echo e(route('suadmin.dashboard')); ?>">Dashboard</a></li>
                    <li>Pengaduan</li>
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
                        <i class="fas fa-comments fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count()); ?></h4>
                        <p class="mb-0">Total Pengaduan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-warning text-warning-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count()); ?></h4>
                        <p class="mb-0">Menunggu Ditinjau</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-success text-success-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'resolved')->count()); ?></h4>
                        <p class="mb-0">Terselesaikan</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-info text-info-content shadow-xl">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="shrink-0 mr-3">
                        <i class="fas fa-bullhorn fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-2xl font-bold"><?php echo e(\App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('type', 'saran')->count()); ?></h4>
                        <p class="mb-0">Saran/Masukan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-header bg-base-200 text-base-content">
            <h4 class="card-title mb-0">Daftar Pengaduan</h4>
            <a href="<?php echo e(route('suadmin.complaints.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Pengaduan
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('suadmin.complaints.index')); ?>" class="mb-4">
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
                        <label for="type" class="label">
                            <span class="label-text">Jenis</span>
                        </label>
                        <select name="type" id="type" class="select select-bordered w-full">
                            <option value="">Semua Jenis</option>
                            <option value="pengaduan" <?php echo e(request('type') == 'pengaduan' ? 'selected' : ''); ?>>Pengaduan</option>
                            <option value="saran" <?php echo e(request('type') == 'saran' ? 'selected' : ''); ?>>Saran</option>
                        </select>
                    </div>
                    <div>
                        <label for="service_id" class="label">
                            <span class="label-text">Layanan</span>
                        </label>
                        <select name="service_id" id="service_id" class="select select-bordered w-full">
                            <option value="">Semua Layanan</option>
                            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>>
                                    <?php echo e($service->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari pengaduan..." value="<?php echo e(request('search')); ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Complaints Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Pengaduan</th>
                            <th>Jenis</th>
                            <th>Subjek</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Ditugaskan Ke</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $complaints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $complaint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($complaint->complaint_number); ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($complaint->type == 'pengaduan'): ?> badge-error
                                    <?php else: ?> badge-success <?php endif; ?>">
                                    <?php echo e(ucfirst($complaint->type)); ?>

                                </span>
                            </td>
                            <td>
                                <strong><?php echo e(Str::limit($complaint->subject, 30)); ?></strong><br>
                                <span class="text-sm opacity-50"><?php echo e(Str::limit($complaint->description, 50)); ?></span>
                            </td>
                            <td><?php echo e($complaint->service ? $complaint->service->name : 'Tidak Ada'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($complaint->status == 'pending'): ?> badge-warning
                                    <?php elseif($complaint->status == 'in_review'): ?> badge-info
                                    <?php elseif($complaint->status == 'resolved'): ?> badge-success
                                    <?php else: ?> badge-neutral <?php endif; ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $complaint->status))); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php if($complaint->priority == 'low'): ?> badge-success
                                    <?php elseif($complaint->priority == 'normal'): ?> badge-info
                                    <?php elseif($complaint->priority == 'high'): ?> badge-warning
                                    <?php else: ?> badge-error <?php endif; ?>">
                                    <?php echo e(ucfirst($complaint->priority)); ?>

                                </span>
                            </td>
                            <td><?php echo e($complaint->assignedTo ? $complaint->assignedTo->name : 'Belum Ditugaskan'); ?></td>
                            <td><?php echo e($complaint->created_at->format('d M Y H:i')); ?></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                        <li>
                                            <a href="<?php echo e(route('suadmin.complaints.show', $complaint)); ?>">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo e(route('suadmin.complaints.edit', $complaint)); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="<?php echo e(route('suadmin.complaints.destroy', $complaint)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengaduan ini?')">
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
                            <td colspan="10" class="text-center py-16">
                                <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                <h5 class="text-lg font-bold text-base-content/70">Tidak ada data pengaduan.</h5>
                                <p class="text-base-content/50">Silakan tambahkan pengaduan baru atau sesuaikan filter pencarian Anda</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-4">
                <div class="mb-2 md:mb-0">
                    <p class="mb-0 text-base-content/70">
                        Menampilkan <?php echo e($complaints->firstItem()); ?> sampai <?php echo e($complaints->lastItem()); ?> 
                        dari <?php echo e($complaints->total()); ?> pengaduan
                    </p>
                </div>
                <div>
                    <?php echo e($complaints->withQueryString()->links('vendor.pagination.daisyui')); ?>

                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="flex flex-wrap justify-center gap-2 mt-3">
                <div class="badge badge-outline badge-primary">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <?php echo e(\App\Models\Complaint::where('type', 'pengaduan')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count()); ?> Pengaduan
                </div>
                <div class="badge badge-outline badge-success">
                    <i class="fas fa-lightbulb mr-1"></i>
                    <?php echo e(\App\Models\Complaint::where('type', 'saran')->where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->count()); ?> Saran
                </div>
                <div class="badge badge-outline badge-info">
                    <i class="fas fa-comments mr-1"></i>
                    <?php echo e(\App\Models\Complaint::where(function($q) { $q->where('is_whistleblowing', false)->orWhere('is_whistleblowing', null); })->where('status', 'pending')->count()); ?> Menunggu
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\complaints\index.blade.php ENDPATH**/ ?>