<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold"><?php echo e($survey->name); ?></h1>
                        <a href="<?php echo e(route('supervision.surveys.dashboard')); ?>" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Surveys
                        </a>
                    </div>
                    
                    <p class="mb-6"><?php echo e($survey->description); ?></p>
                    
                    <form method="POST" action="<?php echo e(route('supervision.spak.submit')); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <?php $__currentLoopData = $survey->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-6 p-4 border rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e($loop->iteration); ?>. <?php echo e($question->question_text); ?>

                                    <?php if($question->is_required): ?>
                                        <span class="text-red-500">*</span>
                                    <?php endif; ?>
                                </label>
                                
                                <?php if($question->question_type === 'rating'): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <label class="flex items-center bg-gray-100 px-3 py-2 rounded cursor-pointer hover:bg-gray-200">
                                                <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="<?php echo e($i); ?>"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                       <?php if($question->is_required): ?> required <?php endif; ?>>
                                                <span class="ml-2"><?php echo e($i); ?></span>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>1 (Strongly Disagree)</span>
                                        <span>5 (Strongly Agree)</span>
                                    </div>
                                <?php elseif($question->question_type === 'yes_no'): ?>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="Yes"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                   <?php if($question->is_required): ?> required <?php endif; ?>>
                                            <span class="ml-2">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="No"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                   <?php if($question->is_required): ?> required <?php endif; ?>>
                                            <span class="ml-2">No</span>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <textarea name="answers[<?php echo e($question->id); ?>]" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                              rows="3" <?php if($question->is_required): ?> required <?php endif; ?>></textarea>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Submit Survey
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\spak-form.blade.php ENDPATH**/ ?>