<?php $__env->startSection('title', 'Manajemen Tiket'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-ticket-alt fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::count()); ?></h4>
                            <p class="mb-0 text-white">Total Tiket</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::where('status', 'pending')->count()); ?></h4>
                            <p class="mb-0 text-white">Menunggu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-sync fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::where('status', 'in_process')->count()); ?></h4>
                            <p class="mb-0 text-white">Sedang Diproses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::where('status', 'pending_approval')->count()); ?></h4>
                            <p class="mb-0 text-white">Menunggu Approval</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-check-double fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::where('status', 'completed')->count()); ?></h4>
                            <p class="mb-0 text-white">Selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::where('status', 'rejected')->count()); ?></h4>
                            <p class="mb-0 text-white">Ditolak</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-light text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark"><?php echo e(\App\Models\Ticket::where('status', 'approved')->count()); ?></h4>
                            <p class="mb-0 text-dark">Disetujui</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-white"><?php echo e(\App\Models\Ticket::whereNotNull('actual_completion_date')->count()); ?></h4>
                            <p class="mb-0 text-white">Sudah Dikerjakan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-header bg-primary text-primary-content flex justify-between items-center">
            <h4 class="card-title mb-0">Manajemen Tiket</h4>
            <a href="<?php echo e(route('suadmin.tickets.create')); ?>" class="btn btn-neutral">
                <i class="fas fa-plus"></i> Tambah Tiket Baru
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <select class="select select-bordered w-full" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="in_progress" <?php echo e(request('status') == 'in_progress' ? 'selected' : ''); ?>>Diproses</option>
                        <option value="pending_approval" <?php echo e(request('status') == 'pending_approval' ? 'selected' : ''); ?>>Menunggu Approval</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Disetujui</option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Selesai</option>
                        <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Ditolak</option>
                    </select>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text">Layanan</span>
                    </label>
                    <select class="select select-bordered w-full" id="serviceFilter">
                        <option value="">Semua Layanan</option>
                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>><?php echo e($service->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text">Mode</span>
                    </label>
                    <select class="select select-bordered w-full" id="modeFilter">
                        <option value="">Semua Mode</option>
                        <option value="online" <?php echo e(request('mode') == 'online' ? 'selected' : ''); ?>>Online</option>
                        <option value="offline" <?php echo e(request('mode') == 'offline' ? 'selected' : ''); ?>>Offline</option>
                        <option value="hybrid" <?php echo e(request('mode') == 'hybrid' ? 'selected' : ''); ?>>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text">Status Persetujuan</span>
                    </label>
                    <select class="select select-bordered w-full" id="approvalFilter">
                        <option value="">Semua Status</option>
                        <option value="approved" <?php echo e(request('approval_status') == 'approved' ? 'selected' : ''); ?>>Disetujui</option>
                        <option value="not_approved" <?php echo e(request('approval_status') == 'not_approved' ? 'selected' : ''); ?>>Belum Disetujui</option>
                        <option value="pending_approval" <?php echo e(request('approval_status') == 'pending_approval' ? 'selected' : ''); ?>>Menunggu Persetujuan</option>
                        <option value="approval_not_required" <?php echo e(request('approval_status') == 'approval_not_required' ? 'selected' : ''); ?>>Tidak Perlu Disetujui</option>
                    </select>
                </div>
                <div class="col-span-full flex justify-end">
                    <button class="btn btn-primary" id="applyFilters">Terapkan Filter</button>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Nomor Tiket</th>
                            <th>Pemohon</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Mode</th>
                            <th>Status Persetujuan</th>
                            <th>Status Survei</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Diperbarui</th>
                            <th>Petugas Pembaruan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($ticket->ticket_number); ?></td>
                                <td><?php echo e($ticket->user->name ?? 'N/A'); ?></td>
                                <td><?php echo e($ticket->service->name ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php if($ticket->status == 'pending'): ?> badge-warning
                                        <?php elseif($ticket->status == 'in_progress'): ?> badge-info
                                        <?php elseif($ticket->status == 'pending_approval'): ?> badge-primary
                                        <?php elseif($ticket->status == 'approved'): ?> badge-success
                                        <?php elseif($ticket->status == 'completed'): ?> badge-success
                                        <?php elseif($ticket->status == 'rejected'): ?> badge-error
                                        <?php else: ?> badge-neutral <?php endif; ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php if($ticket->priority == 'low'): ?> badge-success
                                        <?php elseif($ticket->priority == 'normal'): ?> badge-info
                                        <?php elseif($ticket->priority == 'high'): ?> badge-warning
                                        <?php elseif($ticket->priority == 'urgent'): ?> badge-error
                                        <?php else: ?> badge-neutral <?php endif; ?>">
                                        <?php echo e(ucfirst($ticket->priority)); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?php if($ticket->mode == 'online'): ?> badge-success
                                        <?php elseif($ticket->mode == 'offline'): ?> badge-warning
                                        <?php else: ?> badge-info <?php endif; ?>">
                                        <?php echo e(ucfirst($ticket->mode)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($ticket->approval_required): ?>
                                        <?php if($ticket->is_approved): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Disetujui
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Menunggu
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">
                                            <i class="fas fa-minus-circle"></i> Tidak Perlu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($ticket->hasSurveyCompleted()): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Sudah
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-times-circle"></i> Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($ticket->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($ticket->updated_at ? $ticket->updated_at->format('d M Y H:i') : '-'); ?></td>
                                <td><?php echo e($ticket->updater ? $ticket->updater->name : '-'); ?></td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                            <li>
                                                <a href="<?php echo e(route('suadmin.tickets.show', $ticket)); ?>">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </li>
                                            <li>
                                                <a href="<?php echo e(route('suadmin.tickets.edit', $ticket)); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a href="<?php echo e(route('suadmin.tickets.show', $ticket)); ?>" title="Kirim Info Tiket">
                                                    <i class="fas fa-envelope"></i> Info
                                                </a>
                                            </li>
                                            <?php if($ticket->hasSurveyCompleted()): ?>
                                                <li>
                                                    <a href="javascript:void(0)" class="disabled" title="Survei Telah Selesai">
                                                        <i class="fas fa-paper-plane"></i> Survei
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                    <a href="javascript:void(0)" title="Kirim Info Survei" onclick="sendSurveyInfo(<?php echo e($ticket->id); ?>)">
                                                        <i class="fas fa-paper-plane"></i> Survei
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <form action="<?php echo e(route('suadmin.tickets.destroy', $ticket)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket ini?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-error">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center py-16">
                                    <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                    <h5 class="text-lg font-bold text-base-content/70">Tidak Ada Tiket Ditemukan</h5>
                                    <p class="text-base-content/50">Silakan tambahkan tiket baru atau sesuaikan filter pencarian Anda</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-4">
                <div class="mb-2 md:mb-0">
                    <p class="mb-0 text-base-content/70">
                        Menampilkan <?php echo e($tickets->firstItem()); ?> sampai <?php echo e($tickets->lastItem()); ?> 
                        dari <?php echo e($tickets->total()); ?> data
                    </p>
                </div>
                <div>
                    <?php echo e($tickets->withQueryString()->links('vendor.pagination.daisyui')); ?>

                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="flex flex-wrap justify-center gap-2 mt-3">
                <div class="badge badge-outline badge-primary">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?php echo e(\App\Models\Ticket::where('mode', 'online')->count()); ?> Tiket Online
                </div>
                <div class="badge badge-outline badge-warning">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?php echo e(\App\Models\Ticket::where('mode', 'offline')->count()); ?> Tiket Offline
                </div>
                <div class="badge badge-outline badge-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?php echo e(\App\Models\Ticket::where('mode', 'hybrid')->count()); ?> Tiket Hybrid
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const status = document.getElementById('statusFilter').value;
        const service = document.getElementById('serviceFilter').value;
        const mode = document.getElementById('modeFilter').value;
        const approval = document.getElementById('approvalFilter').value;
        
        let url = '<?php echo e(route('suadmin.tickets.index')); ?>';
        const params = [];
        
        if (status) params.push('status=' + status);
        if (service) params.push('service_id=' + service);
        if (mode) params.push('mode=' + mode);
        if (approval) params.push('approval_status=' + approval);
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
});

function sendSurveyInfo(ticketId) {
    if (confirm('Kirim informasi survei melalui email?')) {
        // This would make an AJAX call to send the survey info email
        fetch(`/suadmin/tickets/${ticketId}/send-survey-info`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email berhasil dikirim');
            } else {
                alert('Gagal mengirim email: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error terjadi saat mengirim email');
        });
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\tickets\index.blade.php ENDPATH**/ ?>