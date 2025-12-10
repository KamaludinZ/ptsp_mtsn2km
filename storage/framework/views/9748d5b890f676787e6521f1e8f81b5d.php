<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Surveys Management</h1>
                    
                    <?php if($surveys->isEmpty()): ?>
                        <p class="text-gray-500">No surveys available.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php $__currentLoopData = $surveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h2 class="text-lg font-semibold"><?php echo e($survey->name); ?></h2>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo e($survey->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo e($survey->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-4"><?php echo e($survey->description); ?></p>
                                    
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-500">Type: <?php echo e($survey->type === 'skm' ? 'Survei Kepuasan Masyarakat' : ($survey->type === 'spak' ? 'Survei Persepsi Anti Korupsi' : ucfirst($survey->type))); ?></p>
                                        <p class="text-sm text-gray-500">Period: <?php echo e($survey->start_date->format('d M Y')); ?> - <?php echo e($survey->end_date ? $survey->end_date->format('d M Y') : 'Ongoing'); ?></p>
                                    </div>
                                    
                                    <?php if($survey->is_active): ?>
                                        <?php if($survey->type === 'skm'): ?>
                                            <a href="<?php echo e(route('onlineportal.skm.form')); ?>" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        <?php elseif($survey->type === 'spak'): ?>
                                            <a href="<?php echo e(route('supervision.spak.form')); ?>" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('supervision.survey.form', $survey->id)); ?>" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Survey not available</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\surveys-dashboard.blade.php ENDPATH**/ ?>