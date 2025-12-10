<?php $__env->startSection('title', $pengumuman->title . ' - ' . config('app.name', 'PTSP MTsN 2 Kota Malang')); ?>

<?php $__env->startPush('styles'); ?>
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/pengumuman.css']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/pengumuman.css'); ?>

    <?php endif; ?>
    <style>
        /* Green badge for category */
        .badge-category-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white;
            border: none;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        /* Dark mode support */
        [data-theme="dark"] .badge-category-green {
            background: #059669 !important;
            color: white !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Detail <span style="color: var(--bs-primary);">Pengumuman</span>
        </h1>
        <p class="lead text-muted">
            Informasi lengkap tentang pengumuman terpilih
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Back Button -->
            <div class="mb-4" data-aos="fade-up">
                <a href="<?php echo e(route('pengumuman.index')); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali ke Arsip Pengumuman
                </a>
            </div>

            <!-- Announcement Detail -->
            <article class="card bg-base-100 shadow-xl" data-aos="fade-up">
                <div class="card-body p-8">
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="badge badge-category-green"><?php echo e(strtoupper($pengumuman->category ?? 'Umum')); ?></div>
                        <div class="text-sm text-base-content/70">
                            <i class="fas fa-calendar mr-1"></i>
                            Diterbitkan: <?php echo e($pengumuman->publish_date->format('d M Y')); ?>

                            <?php if($pengumuman->end_date): ?>
                                <br>
                                <i class="fas fa-clock mr-1"></i>
                                Berakhir: <?php echo e($pengumuman->end_date->format('d M Y')); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <h1 class="text-3xl font-bold mb-4">
                        <?php echo e($pengumuman->title); ?>

                    </h1>

                    <div class="flex flex-wrap justify-between items-center mb-4 text-sm text-base-content/70">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-2"></i>
                            <span><?php echo e($pengumuman->author ?? 'Admin'); ?></span>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="flex items-center gap-2">
                                <?php if($pengumuman->attachment): ?>
                                <a href="<?php echo e(asset('storage/' . $pengumuman->attachment)); ?>"
                                   target="_blank"
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-file-pdf mr-1"></i>PDF
                                </a>
                                <?php endif; ?>
                                <?php if($pengumuman->url): ?>
                                <a href="<?php echo e($pengumuman->url); ?>"
                                   target="_blank"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i>URL
                                </a>
                                <?php endif; ?>
                                <span>
                                    <i class="fas fa-eye mr-2"></i>
                                    <span>Dilihat <?php echo e($pengumuman->view_count); ?> kali</span>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="prose max-w-none mt-4">
                        <?php echo nl2br(e($pengumuman->content)); ?>

                    </div>
                </div>
            </article>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\pengumuman\show.blade.php ENDPATH**/ ?>