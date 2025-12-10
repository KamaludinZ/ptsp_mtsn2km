<?php $__env->startSection('title', __('IP Blocked')); ?>
<?php $__env->startSection('code', '🚫'); ?>
<?php $__env->startSection('message'); ?>
    <p class="error-message">
        Alamat IP Anda (<?php echo e(request()->ip()); ?>) telah diblokir dari mengakses sistem ini.
    </p>

    <div class="error-details" style="margin-top: 1.5rem; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
        <h3 style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">Detail Blokir:</h3>
        <p style="margin-bottom: 0.5rem;"><strong>Alasan:</strong> <?php echo e($reason ?? 'Aktivitas mencurigakan terdeteksi'); ?></p>
        <?php if(isset($expires_at) && $expires_at): ?>
            <p style="margin-bottom: 0.5rem;"><strong>Blokir berakhir:</strong> <?php echo e(\Carbon\Carbon::parse($expires_at)->format('d M Y H:i')); ?> (<?php echo e(\Carbon\Carbon::parse($expires_at)->diffForHumans()); ?>)</p>
        <?php else: ?>
            <p style="margin-bottom: 0.5rem;"><strong>Jenis blokir:</strong> Permanen</p>
        <?php endif; ?>
    </div>

    <div class="error-details" style="margin-top: 1.5rem; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto; background: #fffbe6; border-left-color: #f59e0b;">
        <h3 style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">Merasa ini kesalahan?</h3>
        <p>Silakan hubungi administrator sistem dengan menyertakan informasi di bawah ini:</p>
        <ul style="font-size: 0.875rem; padding-left: 1.25rem; margin-top: 0.5rem;">
            <li><strong>IP Address:</strong> <?php echo e(request()->ip()); ?></li>
            <li><strong>Waktu:</strong> <?php echo e(now()->format('d M Y H:i:s')); ?></li>
        </ul>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\errors\blocked.blade.php ENDPATH**/ ?>