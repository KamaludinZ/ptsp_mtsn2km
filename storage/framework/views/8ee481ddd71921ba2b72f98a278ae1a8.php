<?php $__env->startSection('title', 'Buku Tamu - ' . config('app.name', 'PTSP MTsN 2 Kota Malang')); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Hero Section */
    .visitor-hero {
        background: linear-gradient(135deg, #15803d 0%, #166534 50%, #14532d 100%);
        color: white;
        padding: 3rem 0 2rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 0;
    }

    .visitor-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        z-index: 0;
    }

    .visitor-hero .container {
        position: relative;
        z-index: 1;
    }

    .visitor-hero .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }

    .visitor-hero .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.6);
    }

    /* Stats Cards */
    .stat-card-visitor {
        background: white;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    .stat-card-visitor:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        border-color: rgba(21, 128, 61, 0.2);
    }

    .stat-card-visitor .stat-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.25rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-card-visitor .stat-value {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .stat-card-visitor .stat-label {
        font-size: 1rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Tabs */
    .nav-tabs-visitor {
        border: none;
        gap: 1rem;
        margin-bottom: -1px;
    }

    .nav-tabs-visitor .nav-link {
        border: 2px solid transparent;
        border-radius: 12px 12px 0 0;
        padding: 1rem 2rem;
        font-weight: 600;
        color: var(--bs-gray-600);
        background: var(--bs-gray-100);
        transition: all 0.3s ease;
    }

    .nav-tabs-visitor .nav-link:hover {
        background: var(--bs-gray-200);
        color: var(--bs-gray-800);
    }

    .nav-tabs-visitor .nav-link.active {
        background: white;
        color: var(--bs-primary);
        border-color: var(--bs-gray-300) var(--bs-gray-300) white;
    }

    /* Form Card */
    .visitor-form-card {
        background: white;
        border-radius: 0 16px 16px 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--bs-gray-200);
        min-height: 400px;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
    }

    /* Tab Content */
    .tab-content {
        padding-top: 1rem;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane {
        display: none !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane.active {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .tab-pane.show {
        display: block !important;
    }

    /* Table */
    .visitor-table {
        background: white;
    }

    .visitor-table thead th {
        background: var(--bs-gray-50);
        color: var(--bs-gray-700);
        font-weight: 600;
        border-bottom: 2px solid var(--bs-gray-300);
    }

    .visitor-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .visitor-table tbody tr:hover {
        background-color: var(--bs-gray-50);
    }

    /* Badges */
    .badge-active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-finished {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Dark Mode */
    [data-theme="dark"] body {
        background-color: #111827 !important;
    }

    [data-theme="dark"] .stat-card-visitor {
        background: #1f2937 !important;
        border: 1px solid #374151;
    }

    [data-theme="dark"] .stat-card-visitor .stat-value {
        color: white !important;
    }

    [data-theme="dark"] .stat-card-visitor .stat-label {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link {
        background: #1f2937;
        color: #9ca3af;
    }

    [data-theme="dark"] .nav-tabs-visitor .nav-link.active {
        background: #374151;
        color: white;
        border-color: #4b5563 #4b5563 #374151;
    }

    [data-theme="dark"] .visitor-form-card {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .card {
        background: #1f2937 !important;
        border-color: #374151 !important;
        color: white !important;
    }

    [data-theme="dark"] .visitor-table {
        background: #1f2937 !important;
    }

    [data-theme="dark"] .visitor-table thead th {
        background: #374151 !important;
        color: white !important;
        border-bottom-color: #4b5563 !important;
    }

    [data-theme="dark"] .visitor-table tbody tr:hover {
        background-color: #374151 !important;
    }

    [data-theme="dark"] .visitor-table tbody td {
        color: #d1d5db !important;
        border-bottom-color: #374151 !important;
    }

    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: white !important;
    }

    [data-theme="dark"] p,
    [data-theme="dark"] .text-muted {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .form-label {
        color: white !important;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] textarea {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
        color: white !important;
    }

    [data-theme="dark"] .form-check-label {
        color: #d1d5db !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Page Header -->
<div class="visitor-hero">
    <div class="container">
        <div class="text-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="<?php echo e(url('/')); ?>" class="text-white">Beranda</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Buku Tamu</li>
                </ol>
            </nav>
            <h1 class="display-4 fw-bold mb-3">📋 Buku Tamu Digital</h1>
            <p class="lead mb-4 opacity-90">Sistem pencatatan kunjungan tamu MTsN 2 Kota Malang</p>
        </div>

        <!-- Stats -->
        <div class="row g-4 justify-content-center mt-4">
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%);">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="stat-value"><?php echo e($visitors->total()); ?></div>
                    <div class="stat-label">Total Tamu</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-user-check text-white"></i>
                    </div>
                    <div class="stat-value"><?php echo e($visitors->whereNull('check_out_time')->count()); ?></div>
                    <div class="stat-label">Sedang Aktif</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card-visitor">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div class="stat-value"><?php echo e($visitors->whereNotNull('check_out_time')->count()); ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Tabs -->
    <ul class="nav nav-tabs nav-tabs-visitor mb-0" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="visitor-tab" data-bs-toggle="tab" data-bs-target="#visitor-form" type="button" role="tab">
                <i class="fas fa-user-friends me-2"></i>Formulir Tamu Kunjungan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="applicant-tab" data-bs-toggle="tab" data-bs-target="#applicant-form" type="button" role="tab">
                <i class="fas fa-file-alt me-2"></i>Formulir Tamu Pemohon Layanan Offline
            </button>
        </li>
    </ul>

    <div class="visitor-form-card p-4">
        <div class="tab-content">
            <!-- Visitor Form -->
            <div class="tab-pane fade show active" id="visitor-form" role="tabpanel">
                <form action="<?php echo e(route('public.visitor.submit')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i>Nama Lengkap *
                            </label>
                            <input type="text" name="name" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-primary"></i>No. Telepon/HP *
                            </label>
                            <input type="tel" name="phone" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email
                            </label>
                            <input type="email" name="email" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-2 text-primary"></i>Jenis Instansi/Perusahaan
                            </label>
                            <select name="institution_category" class="form-select form-select-lg">
                                <option value="">Pilih Kategori</option>
                                <optgroup label="Instansi">
                                    <option value="pemerintah">Pemerintah</option>
                                    <option value="swasta">Swasta</option>
                                    <option value="pendidikan">Pendidikan</option>
                                </optgroup>
                                <optgroup label="Perusahaan">
                                    <option value="umkm">UMKM</option>
                                    <option value="menengah">Menengah</option>
                                    <option value="besar">Besar</option>
                                    <option value="multinasional">Multinasional</option>
                                </optgroup>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building me-2 text-primary"></i>Instansi/Perusahaan
                            </label>
                            <input type="text" name="institution" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bullseye me-2 text-primary"></i>Tujuan Kunjungan *
                            </label>
                            <input type="text" name="purpose" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>Keperluan Lainnya
                            </label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="obscure_name" class="form-check-input" id="obscure1">
                                <label class="form-check-label" for="obscure1">
                                    <i class="fas fa-user-secret me-2"></i>Samarkan Nama di Daftar Tamu
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-check-circle me-2"></i>Daftar Tamu
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Service Applicant Form -->
            <div class="tab-pane fade" id="applicant-form" role="tabpanel">
                <form action="<?php echo e(route('public.applicant.submit')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i>Nama Lengkap *
                            </label>
                            <input type="text" name="name" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-phone me-2 text-primary"></i>No. Telepon/HP *
                            </label>
                            <input type="tel" name="phone" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email
                            </label>
                            <input type="email" name="email" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-card me-2 text-primary"></i>Status Pemohon
                            </label>
                            <select name="applicant_type" id="applicant_type" class="form-select form-select-lg">
                                <option value="">Pilih Status</option>
                                <option value="siswa">Siswa</option>
                                <option value="wali_murid">Wali Murid</option>
                                <option value="alumni">Alumni</option>
                                <option value="pegawai">Pegawai</option>
                                <option value="umum">Masyarakat Umum</option>
                                <option value="instansi_perusahaan">Instansi/Perusahaan</option>
                            </select>
                        </div>
                        <div class="col-12 hidden" id="institution-fields">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-building me-2 text-primary"></i>Instansi/Perusahaan
                                    </label>
                                    <input type="text" name="institution" class="form-control form-control-lg">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag me-2 text-primary"></i>Jenis Instansi/Perusahaan
                                    </label>
                                    <select name="institution_category" class="form-select form-select-lg">
                                        <option value="">Pilih Kategori</option>
                                        <optgroup label="Instansi">
                                            <option value="pemerintah">Pemerintah</option>
                                            <option value="swasta">Swasta</option>
                                            <option value="pendidikan">Pendidikan</option>
                                        </optgroup>
                                        <optgroup label="Perusahaan">
                                            <option value="umkm">UMKM</option>
                                            <option value="menengah">Menengah</option>
                                            <option value="besar">Besar</option>
                                            <option value="multinasional">Multinasional</option>
                                        </optgroup>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>Layanan yang Dituju *
                            </label>
                            <input type="text" name="target_service" class="form-control form-control-lg" required placeholder="Contoh: Pengambilan Ijazah, Surat Keterangan, dll">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>Catatan Tambahan
                            </label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="obscure_name" class="form-check-input" id="obscure2">
                                <label class="form-check-label" for="obscure2">
                                    <i class="fas fa-user-secret me-2"></i>Samarkan Nama di Daftar Tamu
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i>Daftar Pemohon Layanan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Visitor List -->
    <div class="card shadow-lg border-0 mt-5">
        <div class="card-body p-4">
            <h2 class="h3 fw-bold mb-4">
                <i class="fas fa-list me-2 text-primary"></i>Daftar Tamu
                <span class="text-primary"><?php echo e(\Carbon\Carbon::parse($date)->translatedFormat('d F Y')); ?></span>
            </h2>
            <div class="table-responsive">
                <table class="table visitor-table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Tamu</th>
                            <th>Instansi</th>
                            <th>Tujuan</th>
                            <th style="width: 100px;">Check In</th>
                            <th style="width: 100px;">Check Out</th>
                            <th style="width: 120px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $visitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="fw-semibold text-muted"><?php echo e(($visitors->currentPage() - 1) * $visitors->perPage() + $index + 1); ?></td>
                                <td>
                                    <div class="fw-bold">
                                        <?php if($visitor->is_obscured): ?>
                                            <script>document.write(obscureName("<?php echo e($visitor->name); ?>"))</script>
                                        <?php else: ?>
                                            <?php echo e($visitor->name); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-phone me-1"></i><?php echo e($visitor->phone); ?>

                                    </div>
                                </td>
                                <td><?php echo e($visitor->institution ?: '-'); ?></td>
                                <td><?php echo e(Str::limit($visitor->purpose, 30)); ?></td>
                                <td>
                                    <i class="fas fa-clock me-1 text-primary"></i>
                                    <?php echo e(\Carbon\Carbon::parse($visitor->check_in_time)->format('H:i')); ?>

                                </td>
                                <td>
                                    <?php if($visitor->check_out_time): ?>
                                        <i class="fas fa-clock me-1 text-warning"></i>
                                        <?php echo e(\Carbon\Carbon::parse($visitor->check_out_time)->format('H:i')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($visitor->check_out_time): ?>
                                        <span class="badge-finished">
                                            <i class="fas fa-check-circle me-1"></i>Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-active">
                                            <i class="fas fa-circle me-1"></i>Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-users text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                        </div>
                                        <h4 class="fw-bold">Belum Ada Tamu</h4>
                                        <p class="text-muted mb-0">Belum ada data tamu untuk tanggal <?php echo e(\Carbon\Carbon::parse($date)->format('d F Y')); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if($visitors->hasPages()): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($visitors->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Function to obscure a name
    function obscureName(name) {
        if (!name) return '';
        const parts = name.split(' ');
        return parts.map(part => {
            if (part.length <= 2) return part;
            return part.charAt(0) + '*'.repeat(part.length - 2) + part.charAt(part.length - 1);
        }).join(' ');
    }

    // Initialize tabs and save active tab to localStorage
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing visitor book tabs...');

        // Function to toggle institution fields visibility
        function toggleInstitutionFields() {
            var applicantTypeSelect = document.getElementById('applicant_type');
            var institutionFields = document.getElementById('institution-fields');
            if (applicantTypeSelect && institutionFields) {
                if (applicantTypeSelect.value === 'instansi_perusahaan') {
                    institutionFields.classList.remove('hidden');
                } else {
                    institutionFields.classList.add('hidden');
                }
            }
        }

        // Call on page load to set initial state
        toggleInstitutionFields();

        // Attach event listener
        var applicantTypeSelect = document.getElementById('applicant_type');
        if (applicantTypeSelect) {
            applicantTypeSelect.addEventListener('change', toggleInstitutionFields);
        }

        // Force show first tab immediately
        const firstTab = document.querySelector('#visitor-form');
        if (firstTab) {
            firstTab.classList.add('show', 'active');
            firstTab.style.display = 'block';
            console.log('✅ First tab force activated');
        }

        // Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.warn('⚠️ Bootstrap is not loaded, using manual tab switching');

            // Manual tab switching
            const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-bs-target');
                    console.log('🖱️ Tab clicked:', targetId);

                    // Hide all tab panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                        pane.style.display = 'none';
                    });

                    // Show target pane
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                        targetPane.style.display = 'block';
                        console.log('✅ Showed tab:', targetId);
                    }

                    // Update nav links
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Save to localStorage
                    localStorage.setItem('visitorBookActiveTab', targetId);
                });
            });
            return;
        }

        const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
        console.log('📋 Found tabs:', tabs.length);

        // Load saved tab
        const savedTab = localStorage.getItem('visitorBookActiveTab');
        if(savedTab) {
            const tabToActivate = document.querySelector(`[data-bs-target="${savedTab}"]`);
            if(tabToActivate) {
                try {
                    const tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                    console.log('💾 Loaded saved tab:', savedTab);
                } catch(e) {
                    console.error('❌ Error loading saved tab:', e);
                }
            }
        }

        // Save tab when changed
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                const target = event.target.getAttribute('data-bs-target');
                localStorage.setItem('visitorBookActiveTab', target);
                console.log('💾 Tab changed to:', target);
            });
        });

        console.log('✅ Visitor book initialization complete!');
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\public\visitor-book.blade.php ENDPATH**/ ?>