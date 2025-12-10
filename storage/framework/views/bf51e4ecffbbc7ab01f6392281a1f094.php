<?php $__env->startSection('title', 'Katalog Layanan - ' . config('app.name', 'PTSP MTsN 2 Kota Malang')); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Service Card Hover Effect */
    .service-card-modern {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none !important;
    }

    .service-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid rgba(20, 83, 45, 0.3) !important;
    }

    .service-item:hover .service-card-modern {
        background-color: rgba(20, 83, 45, 0.02) !important;
    }

    /* Badge Styles */
    .badge-mode {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Service Code Badge - Better centering */
    .badge.bg-white {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        gap: 6px;
    }

    .badge.bg-white i {
        font-size: 12px;
    }

    .badge-online {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .badge-offline {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .badge-hybrid {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    /* Accordion Styles */
    .accordion-button:not(.collapsed) {
        background-color: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(20, 83, 45, 0.2);
    }

    /* Service Meta Icons */
    .service-meta-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    /* Dark mode support - Background */
    [data-theme="dark"] body {
        background-color: #111827 !important;
        color: #f9fafb !important;
    }

    [data-theme="dark"] .container {
        background-color: transparent !important;
    }

    /* Dark mode - Cards */
    [data-theme="dark"] .card {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #f9fafb !important;
    }

    [data-theme="dark"] .card-header {
        background: #374151 !important;
        border-color: #4b5563 !important;
    }

    [data-theme="dark"] .card-body {
        color: #f9fafb !important;
    }

    /* Dark mode - Text colors */
    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: white !important;
    }

    [data-theme="dark"] p {
        color: #d1d5db !important;
    }

    [data-theme="dark"] .text-muted {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .lead {
        color: #d1d5db !important;
    }

    [data-theme="dark"] span[style*="color"] {
        color: white !important;
    }

    /* Dark mode - Badge with inline color */
    [data-theme="dark"] .d-inline-flex[style*="background-color"] {
        background-color: rgba(55, 65, 81, 0.5) !important;
    }

    [data-theme="dark"] .d-inline-flex[style*="background-color"] span {
        color: white !important;
    }

    /* Dark mode - Accordion */
    [data-theme="dark"] .accordion-button {
        background-color: #1f2937 !important;
        color: white !important;
    }

    [data-theme="dark"] .accordion-button:not(.collapsed) {
        background-color: #374151 !important;
        color: white !important;
    }

    [data-theme="dark"] .accordion-body {
        background-color: #1f2937 !important;
        color: #d1d5db !important;
    }

    [data-theme="dark"] .accordion-item {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
    }

    /* Dark mode - Filter Card */
    .filter-card {
        background: linear-gradient(135deg, rgba(20, 83, 45, 0.03) 0%, rgba(234, 88, 12, 0.03) 100%);
        border: 2px solid rgba(20, 83, 45, 0.1);
        border-radius: 16px;
    }

    [data-theme="dark"] .filter-card {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    /* Dark mode - Form controls */
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
        color: white !important;
    }

    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        background-color: #374151 !important;
        border-color: #6b7280 !important;
        color: white !important;
    }

    [data-theme="dark"] .form-label {
        color: white !important;
    }

    /* Dark mode - Buttons */
    [data-theme="dark"] .btn-outline-primary {
        color: white !important;
        border-color: #4b5563 !important;
    }

    [data-theme="dark"] .btn-outline-primary:hover {
        background-color: #374151 !important;
        border-color: #6b7280 !important;
    }

    [data-theme="dark"] .btn-outline-secondary {
        color: white !important;
        border-color: #4b5563 !important;
    }

    /* Dark mode - Stats Grid Background */
    .stats-grid-bg {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(34, 197, 94, 0.05) 100%);
        border-radius: 15px;
        padding: 30px;
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    [data-theme="dark"] .stats-grid-bg {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    /* Dark mode - Alert */
    [data-theme="dark"] .alert-info {
        background-color: #1e3a8a !important;
        border-color: #1e40af !important;
        color: white !important;
    }

    /* Dark mode - Strong and small text */
    [data-theme="dark"] strong {
        color: white !important;
    }

    [data-theme="dark"] small {
        color: #9ca3af !important;
    }

    /* Dark mode - List items */
    [data-theme="dark"] ul li {
        color: #d1d5db !important;
    }

    /* Dark mode - Empty state */
    [data-theme="dark"] .text-center i {
        color: #6b7280 !important;
    }

    /* Dark mode - Service meta icons keep their colored backgrounds */
    [data-theme="dark"] .service-meta-icon {
        /* Icons keep their subtle colored backgrounds */
    }

    /* Dark mode - Badges keep their gradient colors */
    [data-theme="dark"] .badge-online,
    [data-theme="dark"] .badge-offline,
    [data-theme="dark"] .badge-hybrid {
        /* Keep gradient colors for badges */
    }

    /* Dark mode - Border top */
    [data-theme="dark"] .border-top {
        border-color: #374151 !important;
    }

    /* Dark mode - Badge with white background should have dark text */
    [data-theme="dark"] .badge.bg-white {
        background-color: white !important;
        color: #111827 !important;
    }

    [data-theme="dark"] .badge.bg-white i {
        color: #111827 !important;
    }

    [data-theme="dark"] .badge.text-dark {
        color: #111827 !important;
    }

    /* Dark mode - Keep card header gradient backgrounds */
    [data-theme="dark"] .card-header[style*="gradient"] {
        /* Keep the gradient in card headers */
        background: #374151 !important;
    }

    /* Dark mode - Stats grid icons should be white */
    [data-theme="dark"] .stats-grid-bg .card i {
        color: white !important;
    }

    /* Dark mode - Stats grid icon containers keep gradient backgrounds */
    [data-theme="dark"] .stats-grid-bg .card .rounded-circle {
        /* Keep gradient backgrounds for icon containers */
    }

    /* Dark mode - Stats grid heading colors */
    [data-theme="dark"] .stats-grid-bg h3 {
        color: white !important;
    }

    /* Dark mode - All icons in cards */
    [data-theme="dark"] .card i {
        color: white !important;
    }

    /* Dark mode - Icons with specific colors (override for white icons) */
    [data-theme="dark"] .text-white {
        color: white !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
             style="background-color: rgba(20, 83, 45, 0.1);">
            <i class="fas fa-folder-open me-2" style="color: var(--bs-primary);"></i>
            <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Layanan Publik</span>
        </div>
        <h1 class="display-4 fw-bold mb-3">
            Katalog <span style="color: var(--bs-primary);">Layanan</span>
        </h1>
        <p class="lead text-muted">
            Daftar lengkap layanan yang tersedia sesuai dengan
            <span class="fw-semibold" style="color: var(--bs-primary);">Permen PANRB 15/2014</span>
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid-bg" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-4 h-100">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                     style="width: 60px; height: 60px; background: var(--gradient-primary);">
                    <i class="fas fa-layer-group text-white fs-4"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--bs-primary);"><?php echo e($services->count()); ?></h3>
                <p class="text-muted small mb-0">Total Layanan</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-4 h-100">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                     style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-secondary-dark) 100%);">
                    <i class="fas fa-th-large text-white fs-4"></i>
                </div>
                <h3 class="h2 fw-bold mb-1" style="color: var(--bs-secondary);"><?php echo e($categories->count()); ?></h3>
                <p class="text-muted small mb-0">Kategori</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-4 h-100">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-award text-white fs-4"></i>
                </div>
                <h3 class="h2 fw-bold mb-1 text-success">14+</h3>
                <p class="text-muted small mb-0">Standar Pelayanan</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-4 h-100">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <i class="fas fa-clock text-white fs-4"></i>
                </div>
                <h3 class="h2 fw-bold mb-1 text-primary">24/7</h3>
                <p class="text-muted small mb-0">Akses Online</p>
            </div>
        </div>
    </div>
</div>

    <!-- Filter Section -->
    <div class="filter-card p-4 mb-5" data-aos="fade-up" data-aos-delay="200">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">
                    <i class="fas fa-search me-2" style="color: var(--bs-primary);"></i>
                    Cari Layanan
                </label>
                <input type="text"
                       id="searchInput"
                       class="form-control form-control-lg"
                       placeholder="Ketik nama layanan..."
                       onkeyup="filterServices()">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="fas fa-filter me-2" style="color: var(--bs-secondary);"></i>
                    Kategori
                </label>
                <select id="categoryFilter"
                        class="form-select form-select-lg"
                        onchange="filterServices()">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->name); ?>"><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">
                    <i class="fas fa-eye me-2" style="color: #10b981;"></i>
                    Tampilan
                </label>
                <div class="btn-group w-100" role="group">
                    <button class="btn btn-lg btn-outline-primary active" id="gridBtn" onclick="setView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="btn btn-lg btn-outline-primary" id="listBtn" onclick="setView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <small class="text-muted">
                Menampilkan <strong id="serviceCount"><?php echo e($services->count()); ?></strong> dari <?php echo e($services->count()); ?> layanan
            </small>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                <i class="fas fa-redo me-1"></i> Reset
            </button>
        </div>
    </div>

    <!-- Grid View -->
    <div class="row g-4" id="gridView">
        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-6 col-lg-4 service-item"
             data-name="<?php echo e(strtolower($service->name)); ?>"
             data-category="<?php echo e($service->categories->first()->name ?? ''); ?>"
             data-aos="fade-up"
             data-aos-delay="<?php echo e($loop->index * 50); ?>">
            <div class="card shadow-sm service-card-modern h-100">
                <!-- Card Header with Gradient -->
                <div class="card-header border-0 p-4" style="background: var(--gradient-primary);">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-dark px-3 py-2">
                            <i class="fas fa-hashtag"></i> <?php echo e($service->code); ?>

                        </span>
                        <?php if($service->mode === 'online'): ?>
                            <span class="badge badge-mode badge-online">
                                <i class="fas fa-wifi me-1"></i> Online
                            </span>
                        <?php elseif($service->mode === 'offline'): ?>
                            <span class="badge badge-mode badge-offline">
                                <i class="fas fa-store me-1"></i> Offline
                            </span>
                        <?php else: ?>
                            <span class="badge badge-mode badge-hybrid">
                                <i class="fas fa-exchange-alt me-1"></i> Hybrid
                            </span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title text-white mb-2 fw-bold"><?php echo e($service->name); ?></h5>
                    <?php if($service->categories->first()): ?>
                        <small class="text-white-50">
                            <i class="fas fa-tag me-1"></i> <?php echo e($service->categories->first()->name); ?>

                        </small>
                    <?php endif; ?>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4">
                    <p class="text-muted mb-3" style="min-height: 60px;">
                        <?php echo e(Str::limit($service->description, 100)); ?>

                    </p>

                    <!-- Service Meta -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-meta-icon" style="background: rgba(234, 88, 12, 0.1);">
                            <i class="fas fa-clock" style="color: var(--bs-secondary);"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Waktu Proses</small>
                            <strong><?php echo e($service->processing_time ?: '1-3 hari kerja'); ?></strong>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <div class="service-meta-icon" style="background: rgba(20, 83, 45, 0.1);">
                            <i class="fas fa-money-bill-wave" style="color: var(--bs-primary);"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Biaya Layanan</small>
                            <strong><?php echo e($service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis'); ?></strong>
                        </div>
                    </div>

                    <!-- Accordion for Details -->
                    <div class="accordion" id="accordion<?php echo e($service->id); ?>">
                        <div class="accordion-item border-0 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($service->id); ?>">
                                    <i class="fas fa-info-circle me-2"></i> Detail Layanan
                                </button>
                            </h2>
                            <div id="collapse<?php echo e($service->id); ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo e($service->id); ?>">
                                <div class="accordion-body">
                                    <!-- Persyaratan -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">
                                            <i class="fas fa-list-check me-2"></i> Persyaratan
                                        </h6>
                                        <?php if($service->requirements): ?>
                                            <?php $requirements = json_decode($service->requirements, true); ?>
                                            <?php if(is_array($requirements)): ?>
                                                <ul class="list-unstyled">
                                                    <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check text-success me-2"></i> <?php echo e($req); ?>

                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted"><?php echo e($service->requirements); ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted">-</p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Mekanisme -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">
                                            <i class="fas fa-cogs me-2"></i> Sistem & Prosedur
                                        </h6>
                                        <p class="text-muted"><?php echo e($service->mechanism ?? '-'); ?></p>
                                    </div>

                                    <!-- Produk Layanan -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">
                                            <i class="fas fa-file-alt me-2"></i> Produk Layanan
                                        </h6>
                                        <p class="text-muted"><?php echo e($service->product ?? '-'); ?></p>
                                    </div>

                                    <!-- Pengaduan -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">
                                            <i class="fas fa-headset me-2"></i> Pengaduan
                                        </h6>
                                        <p class="text-muted mb-2"><?php echo e($service->complaint_handling ?? 'Hubungi kami melalui halaman pengaduan'); ?></p>
                                        <a href="<?php echo e(route('supervision.complaint.submit')); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-comment-dots me-1"></i> Ajukan Pengaduan
                                        </a>
                                    </div>

                                    <?php if($service->mode === 'online'): ?>
                                        <hr class="my-4">
                                        <?php if(auth()->guard()->check()): ?>
                                            <a href="<?php echo e(route('onlineportal.service.apply', $service->slug)); ?>" class="btn btn-primary w-100">
                                                <i class="fas fa-paper-plane me-2"></i> Ajukan Permohonan
                                            </a>
                                        <?php else: ?>
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Login diperlukan</strong> untuk mengajukan layanan online.
                                                <div class="mt-2">
                                                    <a href="<?php echo e(route('login')); ?>?redirect=<?php echo e(urlencode(route('onlineportal.service.apply', $service->slug))); ?>" class="btn btn-sm btn-primary me-2">
                                                        <i class="fas fa-sign-in-alt me-1"></i> Login
                                                    </a>
                                                    <a href="<?php echo e(route('register')); ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-user-plus me-1"></i> Daftar
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif($service->mode === 'hybrid'): ?>
                                        <?php if($service->external_link): ?>
                                            <a href="<?php echo e($service->external_link); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100 mb-2">
                                                <i class="fas fa-external-link-alt me-2"></i> Akses Layanan Eksternal
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if(auth()->guard()->check()): ?>
                                            <a href="<?php echo e(route('onlineportal.service.apply', $service->slug)); ?>" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-paper-plane me-2"></i> Ajukan Permohonan Internal
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('login')); ?>?redirect=<?php echo e(urlencode(route('onlineportal.service.apply', $service->slug))); ?>" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-sign-in-alt me-2"></i> Login untuk Pengajuan Internal
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- List View -->
    <div class="d-none" id="listView">
        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card shadow-sm service-card-modern mb-4 service-item"
             data-name="<?php echo e(strtolower($service->name)); ?>"
             data-category="<?php echo e($service->categories->first()->name ?? ''); ?>">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle"
                                     style="width: 60px; height: 60px; background: var(--gradient-primary);">
                                    <i class="fas fa-file-alt text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="fw-bold mb-0 me-3"><?php echo e($service->name); ?></h5>
                                    <?php if($service->mode === 'online'): ?>
                                        <span class="badge badge-mode badge-online">Online</span>
                                    <?php elseif($service->mode === 'offline'): ?>
                                        <span class="badge badge-mode badge-offline">Offline</span>
                                    <?php else: ?>
                                        <span class="badge badge-mode badge-hybrid">Hybrid</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted mb-2"><?php echo e(Str::limit($service->description, 150)); ?></p>
                                <div class="d-flex gap-3">
                                    <small class="text-muted">
                                        <i class="fas fa-hashtag me-1"></i> <?php echo e($service->code); ?>

                                    </small>
                                    <?php if($service->categories->first()): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i> <?php echo e($service->categories->first()->name); ?>

                                        </small>
                                    <?php endif; ?>
                                </div>
                                <!-- Time and Cost Display for List View -->
                                <div class="d-flex flex-wrap gap-3 mt-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-meta-icon" style="background: rgba(234, 88, 12, 0.1);">
                                            <i class="fas fa-clock" style="color: var(--bs-secondary);"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Waktu Proses</small>
                                            <strong><?php echo e($service->processing_time ?: '1-3 hari'); ?></strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="service-meta-icon" style="background: rgba(20, 83, 45, 0.1);">
                                            <i class="fas fa-money-bill-wave" style="color: var(--bs-primary);"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Biaya Layanan</small>
                                            <strong><?php echo e($service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#listCollapse<?php echo e($service->id); ?>">
                            <i class="fas fa-chevron-down me-1"></i> Detail
                        </button>
                    </div>
                </div>

                <div class="collapse mt-3" id="listCollapse<?php echo e($service->id); ?>">
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Persyaratan -->
                            <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">
                                <i class="fas fa-list-check me-2"></i> Persyaratan
                            </h6>
                            <?php if($service->requirements): ?>
                                <?php $requirements = json_decode($service->requirements, true); ?>
                                <?php if(is_array($requirements)): ?>
                                    <ul class="list-unstyled">
                                        <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <?php echo e($req); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted"><?php echo e($service->requirements); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted">-</p>
                            <?php endif; ?>

                            <!-- Mekanisme -->
                            <h6 class="fw-bold mb-3 mt-4" style="color: var(--bs-primary);">
                                <i class="fas fa-cogs me-2"></i> Sistem & Prosedur
                            </h6>
                            <p class="text-muted"><?php echo e($service->mechanism ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <!-- Informasi Biaya -->
                            <h6 class="fw-bold mb-3" style="color: var(--bs-primary);">Informasi Biaya</h6>
                            <p class="text-muted"><?php echo e($service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis'); ?></p>

                            <!-- Produk Layanan -->
                            <h6 class="fw-bold mb-3 mt-4" style="color: var(--bs-primary);">
                                <i class="fas fa-file-alt me-2"></i> Produk Layanan
                            </h6>
                            <p class="text-muted"><?php echo e($service->product ?? '-'); ?></p>

                            <!-- Pengaduan -->
                            <h6 class="fw-bold mb-3 mt-4" style="color: var(--bs-primary);">
                                <i class="fas fa-headset me-2"></i> Pengaduan
                            </h6>
                            <p class="text-muted mb-2"><?php echo e($service->complaint_handling ?? 'Hubungi kami melalui halaman pengaduan'); ?></p>
                            <a href="<?php echo e(route('supervision.complaint.submit')); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-comment-dots me-1"></i> Ajukan Pengaduan
                            </a>

                            <!-- Tombol Akses Layanan -->
                            <?php if($service->mode === 'online'): ?>
                                <?php if(auth()->guard()->check()): ?>
                                    <a href="<?php echo e(route('onlineportal.service.apply', $service->slug)); ?>" class="btn btn-primary w-100 mt-3">
                                        <i class="fas fa-paper-plane me-2"></i> Ajukan Permohonan
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Login diperlukan</strong> untuk mengajukan layanan online.
                                        <div class="mt-2">
                                            <a href="<?php echo e(route('login')); ?>?redirect=<?php echo e(urlencode(route('onlineportal.service.apply', $service->slug))); ?>" class="btn btn-sm btn-primary me-2">
                                                <i class="fas fa-sign-in-alt me-1"></i> Login
                                            </a>
                                            <a href="<?php echo e(route('register')); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-user-plus me-1"></i> Daftar
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($service->mode === 'hybrid'): ?>
                                <?php if($service->external_link): ?>
                                    <a href="<?php echo e($service->external_link); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100 mt-3">
                                        <i class="fas fa-external-link-alt me-2"></i> Akses Layanan Eksternal
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(auth()->guard()->check()): ?>
                                    <a href="<?php echo e(route('onlineportal.service.apply', $service->slug)); ?>" class="btn btn-outline-primary w-100 mt-2">
                                        <i class="fas fa-paper-plane me-2"></i> Ajukan Permohonan Internal
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('login')); ?>?redirect=<?php echo e(urlencode(route('onlineportal.service.apply', $service->slug))); ?>" class="btn btn-outline-primary w-100 mt-2">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login untuk Pengajuan Internal
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($services->count() === 0): ?>
    <div class="text-center py-5" data-aos="fade-up">
        <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-muted">Belum ada layanan tersedia</h4>
        <p class="text-muted">Silakan cek kembali nanti</p>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function setView(view) {
    const grid = document.getElementById('gridView');
    const list = document.getElementById('listView');
    const gridBtn = document.getElementById('gridBtn');
    const listBtn = document.getElementById('listBtn');

    if (view === 'grid') {
        grid.classList.remove('d-none');
        list.classList.add('d-none');
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    } else {
        grid.classList.add('d-none');
        list.classList.remove('d-none');
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
    }
}

function filterServices() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const items = document.querySelectorAll('.service-item');
    let count = 0;

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        const cat = item.getAttribute('data-category');
        const matchSearch = !search || name.includes(search);
        const matchCategory = !category || cat === category;

        if (matchSearch && matchCategory) {
            item.classList.remove('d-none');
            count++;
        } else {
            item.classList.add('d-none');
        }
    });

    document.getElementById('serviceCount').textContent = count;
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    filterServices();
}

// Close other accordions when one opens (for both grid and list views)
document.addEventListener('DOMContentLoaded', function() {
    // For grid view accordions
    const gridAccordions = document.querySelectorAll('[id^="collapse"]');
    gridAccordions.forEach(function(accordion) {
        accordion.addEventListener('show.bs.collapse', function() {
            // Close other grid accordions
            gridAccordions.forEach(function(otherAccordion) {
                if (otherAccordion !== accordion && otherAccordion.classList.contains('show')) {
                    otherAccordion.classList.remove('show');
                    const triggerButton = document.querySelector(`[data-bs-target="#${otherAccordion.id}"]`) ||
                                          document.querySelector(`[aria-controls="${otherAccordion.id}"]`);
                    if (triggerButton) {
                        triggerButton.classList.remove('collapsed');
                        triggerButton.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        });
    });

    // For list view accordions
    const listAccordions = document.querySelectorAll('[id^="listCollapse"]');
    listAccordions.forEach(function(accordion) {
        accordion.addEventListener('show.bs.collapse', function() {
            // Close other list accordions
            listAccordions.forEach(function(otherAccordion) {
                if (otherAccordion !== accordion && otherAccordion.classList.contains('show')) {
                    otherAccordion.classList.remove('show');
                    const triggerButton = document.querySelector(`[data-bs-target="#${otherAccordion.id}"]`) ||
                                          document.querySelector(`[aria-controls="${otherAccordion.id}"]`);
                    if (triggerButton) {
                        triggerButton.classList.remove('collapsed');
                        triggerButton.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\onlineportal\service-catalog.blade.php ENDPATH**/ ?>