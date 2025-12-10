<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Service registration was successful.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Service Registration Confirmation</h1>
                    
                    <div class="border border-gray-200 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Ticket Number:</p>
                                <p class="text-xl font-bold text-blue-600"><?php echo e($ticket->ticket_number); ?></p>
                            </div>
                            <div>
                                <p class="font-semibold">Applicant:</p>
                                <p><?php echo e($ticket->user->name); ?></p>
                            </div>
                            <div>
                                <p class="font-semibold">Service:</p>
                                <p><?php echo e($ticket->service->name); ?></p>
                            </div>
                            <div>
                                <p class="font-semibold">Registration Date:</p>
                                <p><?php echo e($ticket->created_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <p class="mb-4">Please inform the applicant about their ticket number for tracking purposes.</p>
                        <p>Estimated completion time: <strong><?php echo e($ticket->service->name); ?></strong> service will be processed according to standard operating procedures.</p>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="<?php echo e(route('frontdesk.register.offline.service.form')); ?>" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Register Another Service
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
<?php echo $__env->make('frontdesk.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\frontdesk\register-offline-service-success.blade.php ENDPATH**/ ?>