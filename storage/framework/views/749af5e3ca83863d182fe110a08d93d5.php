<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">Apply for Service: <?php echo e($service->name); ?></h1>
                        <a href="<?php echo e(route('onlineportal.service.details', $service->id)); ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Details
                        </a>
                    </div>
                    
                    <form method="POST" action="<?php echo e(route('onlineportal.apply.service', $service->id)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-3">Service Details</h2>
                            <p class="mb-4"><?php echo e($service->description); ?></p>
                            
                            <?php if($requirements->count() > 0): ?>
                                <div class="mb-4">
                                    <h3 class="font-medium mb-2">Requirements:</h3>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>
                                                <span class="<?php echo e($requirement->is_required ? 'font-semibold' : ''); ?>">
                                                    <?php echo e($requirement->requirement_name); ?>

                                                </span>
                                                <?php if($requirement->description): ?>
                                                    <div class="text-sm text-gray-600 ml-4"><?php echo e($requirement->description); ?></div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="service_details" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Details
                            </label>
                            <textarea name="service_details" id="service_details" required 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      rows="4" placeholder="Provide any additional details for this service request..."><?php echo e(old('service_details')); ?></textarea>
                            <?php $__errorArgs = ['service_details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Documents (Optional)
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload files</span>
                                            <input id="files" name="files[]" type="file" class="sr-only" multiple>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, JPG, JPEG, PNG up to 10MB
                                    </p>
                                </div>
                            </div>
                            <?php $__errorArgs = ['files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Apply for Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('onlineportal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\onlineportal\apply-service-form.blade.php ENDPATH**/ ?>