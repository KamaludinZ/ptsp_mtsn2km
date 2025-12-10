<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Thank you for completing the survey.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Survey Completed</h1>
                    
                    <div class="border border-gray-200 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Survey:</p>
                                <p class="text-lg font-bold text-blue-600"><?php echo e($survey->name); ?></p>
                            </div>
                            <div>
                                <p class="font-semibold">Submitted:</p>
                                <p><?php echo e(now()->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <p class="mb-4">Your feedback is valuable to us. It will help us improve our services.</p>
                        <p>Survey responses are kept confidential and used only for evaluation and improvement purposes.</p>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="<?php echo e(route('supervision.surveys.dashboard')); ?>" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Take Another Survey
                        </a>
                        <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                            Back to Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\survey-completed.blade.php ENDPATH**/ ?>