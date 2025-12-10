<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Front Desk Dashboard</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Active Visitors</h2>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($activeVisitorsCount ?? 0); ?></p>
                            <a href="<?php echo e(route('frontdesk.active.visitors')); ?>" class="text-blue-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Today's Services</h2>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($todayServicesCount ?? 0); ?></p>
                            <a href="#" class="text-green-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Pending Tickets</h2>
                            <p class="text-3xl font-bold text-yellow-600"><?php echo e($pendingTicketsCount ?? 0); ?></p>
                            <a href="#" class="text-yellow-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow border">
                            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                            <div class="space-y-3">
                                <a href="<?php echo e(route('frontdesk.triage')); ?>" class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                    Triage Visitor
                                </a>
                                <a href="<?php echo e(route('frontdesk.visitor.checkin.form')); ?>" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                    Visitor Check-in
                                </a>
                                <a href="<?php echo e(route('frontdesk.register.offline.service.form')); ?>" class="block w-full text-center bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded">
                                    Register Offline Service
                                </a>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow border">
                            <h2 class="text-lg font-semibold mb-4">Recent Visitors</h2>
                            <ul class="space-y-2">
                                <?php $__empty_1 = true; $__currentLoopData = $recentVisitors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <li class="border-b pb-2">
                                        <span class="font-medium"><?php echo e($visitor->name); ?></span> - <?php echo e($visitor->institution); ?>

                                        <span class="text-sm text-gray-500 ml-2"><?php echo e($visitor->check_in_time->format('H:i')); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <li class="text-gray-500">No recent visitors</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontdesk.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\frontdesk\dashboard.blade.php ENDPATH**/ ?>