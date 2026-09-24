<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Visitor Triage</h1>
                    
                    <p class="mb-6">Is the visitor coming as a <strong>TAMU</strong> (guest) or as a <strong>PEMOHON LAYANAN</strong> (service applicant)?</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg shadow text-center">
                            <h2 class="text-xl font-semibold mb-4">TAMU</h2>
                            <p class="mb-4">For guests such as meeting attendees, vendors, official visitors, etc.</p>
                            <a href="<?php echo e(route('frontdesk.triage')); ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Check-in as Guest
                            </a>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg shadow text-center">
                            <h2 class="text-xl font-semibold mb-4">PEMOHON LAYANAN</h2>
                            <p class="mb-4">For those seeking services such as students requesting letters, parents requesting certification, etc.</p>
                            <a href="<?php echo e(route('frontdesk.service.application')); ?>" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                Register Service Request
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('frontdesk.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/frontdesk/triage.blade.php ENDPATH**/ ?>