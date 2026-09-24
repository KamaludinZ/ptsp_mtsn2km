<?php $__env->startSection('title', 'Manajemen Layanan'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h2 class="text-2xl font-bold text-base-content">
                <i class="bi bi-gear-fill mr-2 text-secondary"></i>Manajemen Layanan
            </h2>
            <p class="text-base-content/70">Kelola semua layanan yang tersedia di sistem PTSP</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="<?php echo e(route('admin.services.create')); ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle-fill mr-2"></i>Tambah Layanan Baru
            </a>
        </div>
    </div>
    
    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card bg-gradient-primary text-primary-content shadow-xl stats-card">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <h6 class="text-uppercase text-white-opacity-75 mb-1">Total Layanan</h6>
                        <h3 class="text-white text-3xl font-bold"><?php echo e($totalServices); ?></h3>
                        <p class="text-white-opacity-75 mb-0">Jumlah layanan keseluruhan</p>
                    </div>
                    <div class="p-3 rounded-full bg-white bg-opacity-25">
                        <i class="fas fa-layer-group fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-success text-success-content shadow-xl stats-card">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <h6 class="text-uppercase text-white-opacity-75 mb-1">Layanan Aktif</h6>
                        <h3 class="text-white text-3xl font-bold"><?php echo e($activeServices); ?></h3>
                        <p class="text-white-opacity-75 mb-0">Layanan yang sedang aktif</p>
                    </div>
                    <div class="p-3 rounded-full bg-white bg-opacity-25">
                        <i class="fas fa-check-circle fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-info text-info-content shadow-xl stats-card">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <h6 class="text-uppercase text-white-opacity-75 mb-1">Layanan Online</h6>
                        <h3 class="text-white text-3xl font-bold"><?php echo e($onlineServices); ?></h3>
                        <p class="text-white-opacity-75 mb-0">Layanan dengan mode online</p>
                    </div>
                    <div class="p-3 rounded-full bg-white bg-opacity-25">
                        <i class="fas fa-globe-americas fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-warning text-warning-content shadow-xl stats-card">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <h6 class="text-uppercase text-white-opacity-75 mb-1">Rata-rata Hari</h6>
                        <h3 class="text-white text-3xl font-bold"><?php echo e(number_format($avgEstimatedDays, 1)); ?></h3>
                        <p class="text-white-opacity-75 mb-0">Durasi rata-rata penyelesaian</p>
                    </div>
                    <div class="p-3 rounded-full bg-white bg-opacity-25">
                        <i class="fas fa-clock fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h5 class="card-title text-base-content"><i class="fas fa-table mr-2"></i> Daftar Layanan</h5>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-sm btn-outline">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li><h6 class="menu-title">Status Layanan</h6></li>
                        <li><a href="?status=active">Hanya Aktif</a></li>
                        <li><a href="?status=inactive">Hanya Tidak Aktif</a></li>
                        <li><div class="divider"></div></li>
                        <li><h6 class="menu-title">Mode Layanan</h6></li>
                        <li><a href="?mode=online">Mode Online</a></li>
                        <li><a href="?mode=offline">Mode Offline</a></li>
                        <li><a href="?mode=both">Mode Both</a></li>
                        <li><div class="divider"></div></li>
                        <li><h6 class="menu-title">Approval</h6></li>
                        <li><a href="?approval=required">Memerlukan Approval</a></li>
                        <li><a href="?approval=not_required">Tidak Perlu Approval</a></li>
                        <li><div class="divider"></div></li>
                        <li><a href="<?php echo e(route('admin.services.index')); ?>">Tampilkan Semua</a></li>
                    </ul>
                </div>
            </div> 
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="label">
                        <span class="label-text">Kategori</span>
                    </label>
                    <select class="select select-bordered w-full shadow-sm" id="categoryFilter">
                        <option value="">Semua Kategori</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                <?php echo e($category->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-2">
                    <label class="label">
                        <span class="label-text">Cari Layanan</span>
                    </label>
                    <div class="join w-full shadow-sm">
                        <input type="text" class="input input-bordered join-item w-full" id="searchInput" placeholder="Cari nama layanan..." value="<?php echo e(request('search')); ?>">
                        <button class="btn btn-primary join-item" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-end">
                    <button class="btn btn-primary w-full shadow-sm" id="applyFilters">
                        <i class="fas fa-filter mr-2"></i>Terapkan Filter
                    </button>
                </div>
            </div>

            <!-- Services Table -->
            <div class="overflow-x-auto rounded-lg shadow-sm">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Slug</th>
                            <th>Kategori</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Digital</th>
                            <th>Approval</th>
                            <th>Estimasi (Hari)</th>
                            <th>Dibuat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar">
                                            <div class="service-icon mask mask-squircle w-12 h-12">
                                                <i class="fas fa-concierge-bell"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold"><?php echo e($service->name); ?></div>
                                            <div class="text-sm opacity-50"><?php echo e(Str::limit($service->description, 50)); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-ghost badge-sm"><?php echo e($service->slug); ?></span></td>
                                <td>
                                    <?php $__currentLoopData = $service->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge badge-primary badge-sm mr-1 mb-1"><?php echo e($category->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td>
                                    <?php
                                        $modeConfig = [
                                            'online' => ['color' => 'success', 'icon' => 'cloud'],
                                            'offline' => ['color' => 'warning', 'icon' => 'store'],
                                            'both' => ['color' => 'info', 'icon' => 'sync']
                                        ];
                                        $config = $modeConfig[$service->mode] ?? ['color' => 'neutral', 'icon' => 'question'];
                                    ?>
                                    <span class="badge badge-<?php echo e($config['color']); ?> badge-sm">
                                        <i class="fas fa-<?php echo e($config['icon']); ?> mr-1"></i>
                                        <?php echo e(ucfirst($service->mode)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($service->is_active): ?>
                                        <span class="badge badge-success badge-sm">
                                            <i class="fas fa-check-circle mr-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-error badge-sm">
                                            <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($service->is_digital_product): ?>
                                        <span class="badge badge-success badge-sm">
                                            <i class="fas fa-file-pdf mr-1"></i> Ya
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral badge-sm">
                                            <i class="fas fa-box mr-1"></i> Tidak
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($service->approval_required ?? true): ?>
                                        <span class="badge badge-primary badge-sm tooltip" data-tip="<?php echo e($service->approval_instructions ?? 'Memerlukan persetujuan dari petugas'); ?>">
                                            <i class="bi bi-shield-check mr-1"></i> Perlu Approval
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral badge-sm">
                                            <i class="bi bi-shield-slash mr-1"></i> Tidak Perlu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-warning badge-sm">
                                        <i class="fas fa-clock mr-1"></i><?php echo e($service->estimated_days ?? 'N/A'); ?> hari
                                    </span>
                                </td>
                                <td><?php echo e($service->created_at->format('d M Y')); ?></td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                            <li>
                                                <a href="<?php echo e(route('admin.services.show', $service)); ?>">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </li>
                                            <li>
                                                <a href="<?php echo e(route('admin.services.edit', $service)); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="<?php echo e(route('admin.services.destroy', $service)); ?>"
                                                      method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
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
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                        <h5 class="text-lg font-bold text-base-content/70">Tidak Ada Layanan Ditemukan</h5>
                                        <p class="text-base-content/50">Silakan tambahkan layanan baru atau sesuaikan filter pencarian Anda</p>
                                        <a href="<?php echo e(route('admin.services.create')); ?>" class="btn btn-primary mt-4">
                                            <i class="fas fa-plus mr-2"></i>Tambah Layanan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                <?php echo e($services->withQueryString()->links('vendor.pagination.daisyui')); ?>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
        const category = document.getElementById('categoryFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = '<?php echo e(route('admin.services.index')); ?>';
        const params = [];
        
        if (category) params.push('category=' + category);
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Also trigger filter when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });
    
    // Also trigger filter when click search button
    document.getElementById('searchButton').addEventListener('click', function() {
        document.getElementById('applyFilters').click();
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/services/index.blade.php ENDPATH**/ ?>