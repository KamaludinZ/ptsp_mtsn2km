<div class="p-6 bg-white rounded-xl border border-gray-200">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sistem</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-blue-800">Aplikasi</div>
            <div class="text-lg font-semibold text-blue-900"><?php echo e($this->getSystemInfo()['app_name']); ?></div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-green-800">Laravel</div>
            <div class="text-lg font-semibold text-green-900"><?php echo e($this->getSystemInfo()['laravel_version']); ?></div>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-purple-800">Filament</div>
            <div class="text-lg font-semibold text-purple-900"><?php echo e($this->getSystemInfo()['filament_version']); ?></div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-yellow-800">PHP</div>
            <div class="text-lg font-semibold text-yellow-900"><?php echo e($this->getSystemInfo()['php_version']); ?></div>
        </div>
        
        <div class="bg-indigo-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-indigo-800">Sistem Operasi</div>
            <div class="text-lg font-semibold text-indigo-900"><?php echo e($this->getSystemInfo()['server_os']); ?></div>
        </div>
        
        <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-red-800">Uptime Server</div>
            <div class="text-lg font-semibold text-red-900"><?php echo e($this->getSystemInfo()['uptime']); ?></div>
        </div>
    </div>
    
    <?php if($this->getSystemInfo()['last_login'] !== 'N/A'): ?>
    <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
            <span class="font-medium">Login Terakhir:</span> <?php echo e($this->getSystemInfo()['last_login']); ?>

        </div>
    </div>
    <?php endif; ?>
</div><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\filament\widgets\system-info-widget.blade.php ENDPATH**/ ?>