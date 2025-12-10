<?php
    $hide_back_button = true;
?>



<?php $__env->startSection('title', 'Perawatan Sistem'); ?>
<?php $__env->startSection('code', '⏳'); ?>
<?php $__env->startSection('icon'); ?>
    <i class="fa-solid fa-tools maintenance-icon"></i>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('message'); ?>
    <p class="error-message">
        Kami sedang melakukan perawatan sistem untuk meningkatkan kualitas layanan kami.
    </p>

    <div class="maintenance-details" style="margin-top: 1.5rem; text-align: center; max-width: 450px; margin-left: auto; margin-right: auto;">
        <p class="error-message">Terima kasih atas pengertian dan kesabaran Anda.</p>

        <div class="maintenance-message" style="margin: 1.5rem 0; padding: 1rem; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center;">
            <p style="font-size: 1.1rem; font-weight: 600; color: #166534; margin: 0; padding: 0;">Sistem sedang dalam perawatan</p>
        </div>

        <div class="contact-info" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
            <p style="font-weight: 600; margin-bottom: 0.5rem;">Untuk informasi lebih lanjut:</p>
            <p>Email: mtsnmalang2adm@gmail.com</p>
            <p>Telepon: (0341) 711500</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Auto-refresh after maintenance period
        setTimeout(function() {
            window.location.reload();
        }, <?php echo e(config('maintenance.retry_after', 300)); ?> * 1000);
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\errors\503.blade.php ENDPATH**/ ?>