<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">Processing Complaint #<?php echo e($complaint->id); ?></h1>
                        <a href="<?php echo e(route('supervision.complaints.dashboard')); ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Dashboard
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Complaint Info</h2>
                            <p><span class="font-medium">Type:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php if($complaint->complaint_type === 'complaint'): ?> bg-red-100 text-red-800
                                    <?php elseif($complaint->complaint_type === 'suggestion'): ?> bg-green-100 text-green-800
                                    <?php else: ?> bg-yellow-100 text-yellow-800
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($complaint->complaint_type)); ?>

                                </span>
                            </p>
                            <p><span class="font-medium">Title:</span> <?php echo e($complaint->title); ?></p>
                            <p><span class="font-medium">Status:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php if($complaint->status === 'resolved' || $complaint->status === 'closed'): ?> bg-green-100 text-green-800
                                    <?php elseif($complaint->status === 'in_progress'): ?> bg-yellow-100 text-yellow-800
                                    <?php elseif($complaint->status === 'in_review'): ?> bg-blue-100 text-blue-800
                                    <?php else: ?> bg-gray-100 text-gray-800
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $complaint->status))); ?>

                                </span>
                            </p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Submitter Info</h2>
                            <?php if($complaint->anonymous): ?>
                                <p><span class="font-medium">Anonymous:</span> Yes</p>
                            <?php else: ?>
                                <p><span class="font-medium">Name:</span> <?php echo e($complaint->complainant_name); ?></p>
                                <p><span class="font-medium">Email:</span> <?php echo e($complaint->complainant_email); ?></p>
                                <?php if($complaint->complainant_contact): ?>
                                    <p><span class="font-medium">Contact:</span> <?php echo e($complaint->complainant_contact); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Assignment</h2>
                            <p><span class="font-medium">Assigned To:</span> <?php echo e($complaint->assignee->name ?? 'Unassigned'); ?></p>
                            <p><span class="font-medium">Submitted:</span> <?php echo e($complaint->created_at->format('d M Y H:i')); ?></p>
                            <?php if($complaint->resolved_at): ?>
                                <p><span class="font-medium">Resolved:</span> <?php echo e($complaint->resolved_at->format('d M Y H:i')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Complaint Description -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Description</h2>
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="whitespace-pre-line"><?php echo e($complaint->description); ?></p>
                        </div>
                    </div>
                    
                    <!-- Resolution Notes -->
                    <?php if($complaint->resolution_notes): ?>
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Resolution Notes</h2>
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <p class="whitespace-pre-line"><?php echo e($complaint->resolution_notes); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Processing Form -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Update Complaint Status</h2>
                        <form method="POST" action="<?php echo e(route('supervision.update.complaint', $complaint->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="status" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="submitted" <?php echo e($complaint->status === 'submitted' ? 'selected' : ''); ?>>Submitted</option>
                                        <option value="in_review" <?php echo e($complaint->status === 'in_review' ? 'selected' : ''); ?>>In Review</option>
                                        <option value="in_progress" <?php echo e($complaint->status === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                                        <option value="resolved" <?php echo e($complaint->status === 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                                        <option value="closed" <?php echo e($complaint->status === 'closed' ? 'selected' : ''); ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                                    <select name="assigned_to" id="assigned_to" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Unassign</option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>" <?php echo e($complaint->assigned_to === $user->id ? 'selected' : ''); ?>>
                                                <?php echo e($user->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700 mb-2">Resolution Notes</label>
                                <textarea name="resolution_notes" id="resolution_notes" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          rows="4" placeholder="Add notes about the resolution..."><?php echo e($complaint->resolution_notes ?? old('resolution_notes')); ?></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                    Update Complaint
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\process-complaint.blade.php ENDPATH**/ ?>