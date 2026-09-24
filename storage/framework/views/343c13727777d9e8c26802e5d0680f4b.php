<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name')); ?></title>

    
    <?php if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        <?php echo App\Helpers\AssetHelper::css('resources/css/app.css'); ?>

        <?php echo App\Helpers\AssetHelper::js('resources/js/app.js', false); ?>

    <?php endif; ?>

    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
        }

        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Desktop Layout - Header di sebelah kiri */
        @media (min-width: 768px) {
            .error-card {
                flex-direction: row;
                min-height: 500px;
            }
        }

        .error-header {
            background: linear-gradient(135deg, #15803d 0%, #1a532d 100%);
            color: white;
            padding: 40px 32px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Desktop - Header occupy 35% width */
        @media (min-width: 768px) {
            .error-header {
                width: 35%;
                padding: 60px 40px;
            }
        }

        .error-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #dcfce7;
        }

        .maintenance-icon {
            font-size: 3rem !important; /* Larger icon for maintenance */
        }

        .error-header-title {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .error-header-subtitle {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.8;
        }

        .error-content {
            flex: 1;
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Desktop - Content occupy 65% width */
        @media (min-width: 768px) {
            .error-content {
                width: 65%;
                padding: 60px 50px;
            }
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            color: #1f2937;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 16px;
        }

        .error-message {
            color: #4b5563;
            font-size: 1rem;
            margin-top: 8px;
            max-width: 400px;
        }

        .btn-back {
            display: inline-block;
            background: #166534;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 32px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-back:hover {
            background: #15803d;
            transform: translateY(-2px);
        }

        .support-info {
            margin-top: 32px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            
            <div class="error-header">
                <div class="error-icon-wrapper">
                    <?php echo $__env->yieldContent('icon', '<i class="fa-solid fa-triangle-exclamation"></i>'); ?>
                </div>
                <h1 class="error-header-title"><?php echo e(config('app.name')); ?></h1>
                <p class="error-header-subtitle">Layanan Terpadu Satu Pintu</p>
            </div>

            
            <div class="error-content">
                <div class="error-code"><?php echo $__env->yieldContent('code', 'Oops!'); ?></div>
                <h2 class="error-title"><?php echo $__env->yieldContent('title'); ?></h2>
                <p class="error-message"><?php echo $__env->yieldContent('message'); ?></p>

                <?php if(!isset($hide_back_button) || $hide_back_button !== true): ?>
                <a href="<?php echo e(app('router')->has('home') ? route('home') : url('/')); ?>" class="btn-back">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
                </a>
                <?php endif; ?>

                <div class="support-info">
                    <p>Jika masalah berlanjut, silakan hubungi administrator sistem.</p>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/errors/layout.blade.php ENDPATH**/ ?>