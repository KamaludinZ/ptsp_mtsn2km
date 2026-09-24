<?php $__env->startSection('title', 'Buku Tamu & Pengunjung'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Visitor::count()); ?></h4>
                            <p class="mb-0 text-white">Total Pengunjung</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-running fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Visitor::whereNull('check_out_time')->count()); ?></h4>
                            <p class="mb-0 text-white">Aktif Saat Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-sign-out-alt fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Visitor::whereNotNull('check_out_time')->count()); ?></h4>
                            <p class="mb-0 text-white">Sudah Checkout</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Visitor::whereDate('check_in_time', today())->count()); ?></h4>
                            <p class="mb-0 text-white">Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Buku Tamu & Pengunjung</h4>
                    <a href="<?php echo e(route('admin.visitors.create')); ?>" class="btn btn-light">
                        <i class="fas fa-plus"></i> Tambah Pengunjung
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Aktif</option>
                                <option value="checked_out" <?php echo e(request('status') == 'checked_out' ? 'selected' : ''); ?>>Sudah Checkout</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="dateFromFilter" value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="dateToFilter" value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cari</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari nama, email, dll..." value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <button class="btn btn-primary" id="applyFilters">Terapkan Filter</button>
                        </div>
                    </div>

                    <!-- Visitors Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Institusi</th>
                                    <th>Tujuan</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $visitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($visitor->name); ?></td>
                                        <td><?php echo e($visitor->email ?? '-'); ?></td>
                                        <td><?php echo e($visitor->phone ?? '-'); ?></td>
                                        <td><?php echo e($visitor->institution ?? '-'); ?></td>
                                        <td><?php echo e($visitor->purpose ?? '-'); ?></td>
                                        <td><?php echo e($visitor->check_in_time ? $visitor->check_in_time->format('d M Y H:i') : '-'); ?></td>
                                        <td><?php echo e($visitor->check_out_time ? $visitor->check_out_time->format('d M Y H:i') : '-'); ?></td>
                                        <td>
                                            <?php if($visitor->check_out_time): ?>
                                                <span class="badge bg-success">Selesai</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('admin.visitors.show', $visitor)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.visitors.edit', $visitor)); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.visitors.destroy', $visitor)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data pengunjung ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada pengunjung ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                        <div class="mb-2 mb-md-0">
                            <p class="mb-0">
                                Menampilkan <?php echo e($visitors->firstItem()); ?> sampai <?php echo e($visitors->lastItem()); ?> 
                                dari <?php echo e($visitors->total()); ?> pengunjung
                            </p>
                        </div>
                        <div>
                            <?php echo e($visitors->withQueryString()->links()); ?>

                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    <?php echo e(\App\Models\Visitor::whereDate('check_in_time', today())->count()); ?> Hari Ini
                                </span>
                                <span class="badge bg-warning-subtle text-warning border border-warning">
                                    <i class="fas fa-calendar-week me-1"></i>
                                    <?php echo e(\App\Models\Visitor::whereBetween('check_in_time', [now()->startOfWeek(), now()->endOfWeek()])->count()); ?> Minggu Ini
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo e(\App\Models\Visitor::whereMonth('check_in_time', now()->month)->count()); ?> Bulan Ini
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const status = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFromFilter').value;
        const dateTo = document.getElementById('dateToFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = '<?php echo e(route('admin.visitors.index')); ?>';
        const params = [];
        
        if (status) params.push('status=' + status);
        if (dateFrom) params.push('date_from=' + dateFrom);
        if (dateTo) params.push('date_to=' + dateTo);
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/visitors/index.blade.php ENDPATH**/ ?>