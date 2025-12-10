<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Visitor check-in was successful.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Visitor Card</h1>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <h2 class="text-xl font-semibold mb-4">VISITOR PASS</h2>
                        
                        <div class="mb-4">
                            <p class="font-semibold"><?php echo e($visitor->name); ?></p>
                            <p class="text-sm text-gray-600"><?php echo e($visitor->institution); ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm">To meet: <?php echo e($visitor->person_to_meet); ?></p>
                            <p class="text-sm">Purpose: <?php echo e($visitor->purpose); ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-lg font-bold text-blue-600"><?php echo e($visitor->visitor_card_number); ?></p>
                            <p class="text-sm">Card Number</p>
                        </div>
                        
                        <div>
                            <p class="text-sm">Check-in: <?php echo e($visitor->check_in_time->format('d M Y H:i')); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-between">
                        <a href="<?php echo e(route('frontdesk.visitor.checkin.form')); ?>" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            New Visitor Check-in
                        </a>
                        <a href="<?php echo e(route('frontdesk.dashboard')); ?>" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontdesk.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\frontdesk\visitor-checkin-success.blade.php ENDPATH**/ ?>