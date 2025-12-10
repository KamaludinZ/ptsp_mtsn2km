<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Terima Kasih</title>
</head>
<body>
    <h1>Terima Kasih telah Mengisi Survei</h1>
    
    <p>Yang Terhormat,</p>
    
    <p>Terima kasih atas partisipasi Anda dalam mengisi survei kepuasan masyarakat. Pendapat Anda sangat berharga bagi kami dalam meningkatkan kualitas pelayanan kami.</p>
    
    <?php if($ticket): ?>
        <p><strong>Nomor Tiket:</strong> <?php echo e($ticket->ticket_number); ?></p>
        <p><strong>Jenis Layanan:</strong> <?php echo e($ticket->service->name ?? 'N/A'); ?></p>
    <?php endif; ?>
    
    <p>Data Anda telah kami terima dan akan kami proses secara rahasia.</p>
    
    <p>Salam,<br>
    <?php echo e(config('app.name')); ?></p>
</body>
</html><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\emails\thank-you-survey.blade.php ENDPATH**/ ?>