<?php $__env->startComponent('mail::message'); ?>
# Pesan Baru dari Formulir Kontak

**Nama:** <?php echo e($data['name']); ?>

**Email:** <?php echo e($data['email']); ?>

**Subjek:** <?php echo e($data['subject']); ?>


**Pesan:**
<?php echo e($data['message']); ?>


Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\emails\contact-form.blade.php ENDPATH**/ ?>