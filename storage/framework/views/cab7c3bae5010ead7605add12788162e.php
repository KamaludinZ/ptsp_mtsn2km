<?php $__env->startSection('title', 'Complaints Management - ' . config('app.name', 'PTSP MTsN 2 Kota Malang')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
        <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Complaints Management</h1>
                        <a href="<?php echo e(route('supervision.complaint.create.form')); ?>" 
                           class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Submit Complaint
                        </a>
                    </div>
                    
                    <?php if($complaints->isEmpty()): ?>
                        <p class="text-gray-500">No complaints found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $complaints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $complaint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($complaint->id); ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($complaint->title); ?></div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo e(\Str::limit($complaint->description, 50)); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php if($complaint->complaint_type === 'complaint'): ?> bg-red-100 text-red-800
                                                    <?php elseif($complaint->complaint_type === 'suggestion'): ?> bg-green-100 text-green-800
                                                    <?php else: ?> bg-yellow-100 text-yellow-800
                                                    <?php endif; ?>">
                                                    <?php echo e(ucfirst($complaint->complaint_type)); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php if($complaint->status === 'resolved' || $complaint->status === 'closed'): ?> bg-green-100 text-green-800
                                                    <?php elseif($complaint->status === 'in_progress'): ?> bg-yellow-100 text-yellow-800
                                                    <?php elseif($complaint->status === 'in_review'): ?> bg-blue-100 text-blue-800
                                                    <?php else: ?> bg-gray-100 text-gray-800
                                                    <?php endif; ?>">
                                                    <?php echo e(ucfirst(str_replace('_', ' ', $complaint->status))); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo e($complaint->assignee->name ?? 'Unassigned'); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo e($complaint->created_at->format('d M Y')); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="<?php echo e(route('supervision.process.complaint', $complaint->id)); ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\complaints-dashboard.blade.php ENDPATH**/ ?>