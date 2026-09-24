<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Back Office Dashboard</h1>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Total Tickets</h2>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($stats['total_tickets']); ?></p>
                        </div>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Pending</h2>
                            <p class="text-3xl font-bold text-yellow-600"><?php echo e($stats['pending_tickets']); ?></p>
                        </div>
                        
                        <div class="bg-orange-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">In Progress</h2>
                            <p class="text-3xl font-bold text-orange-600"><?php echo e($stats['in_progress_tickets']); ?></p>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Completed</h2>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($stats['completed_tickets']); ?></p>
                        </div>
                    </div>
                    
                    <!-- My Tasks -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">My Tasks</h2>
                        
                        <?php if($tickets->isEmpty()): ?>
                            <p class="text-gray-500">No tasks assigned to you at the moment.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo e($ticket->ticket_number); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500"><?php echo e($ticket->user->name); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500"><?php echo e($ticket->service->name); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php if($ticket->status === 'completed'): ?> bg-green-100 text-green-800
                                                        <?php elseif($ticket->status === 'in_process'): ?> bg-yellow-100 text-yellow-800
                                                        <?php elseif($ticket->status === 'approved'): ?> bg-blue-100 text-blue-800
                                                        <?php else: ?> bg-gray-100 text-gray-800
                                                        <?php endif; ?>">
                                                        <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo e($ticket->created_at->format('d M Y')); ?>

                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="<?php echo e(route('backoffice.tickets.detail', $ticket->ticket_number)); ?>" class="text-indigo-600 hover:text-indigo-900">Process</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white p-6 rounded-lg shadow border">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="<?php echo e(route('backoffice.tickets.all')); ?>" class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded">
                                View All Tickets
                            </a>
                            <a href="#" class="block text-center bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded">
                                Generate Report
                            </a>
                            <a href="#" class="block text-center bg-purple-500 hover:bg-purple-600 text-white py-3 px-4 rounded">
                                Manage Workflows
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backoffice.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/backoffice/dashboard.blade.php ENDPATH**/ ?>