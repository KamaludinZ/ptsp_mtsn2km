<?php $__env->startSection('title', 'Survey Kepuasan Masyarakat - Identitas Responden'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Survey Kepuasan Masyarakat
                </h2>
                <p class="text-muted">MTsN 2 Kota Malang</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 1 dari 3</span>
                    <span class="text-muted small">33%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 33%;"
                         aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Tahap 1: Identitas Responden
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Silakan lengkapi data diri Anda terlebih dahulu. Semua informasi akan dijaga kerahasiaannya.
                    </p>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Terjadi kesalahan!</strong>
                            <ul class="mb-0 mt-2">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('survey.step1.store')); ?>" method="POST" id="step1Form">
                        <?php echo csrf_field(); ?>

                        <?php $__currentLoopData = $identityQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <?php echo e($question->question); ?>

                                    <?php if($question->is_required): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>

                                <?php if($question->field_type === 'text'): ?>
                                    <?php if($question->question === 'Nama Lengkap'): ?>
                                        <input type="text"
                                               class="form-control <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               name="answers[<?php echo e($question->id); ?>]"
                                               value="<?php echo e(old('answers.' . $question->id)); ?>"
                                               <?php echo e($question->is_required ? 'required' : ''); ?>>
                                    <?php elseif(stripos($question->question, 'alamat') !== false): ?>
                                        <!-- Skip address field as per requirement -->
                                        <?php continue; ?>
                                    <?php else: ?>
                                        <input type="text"
                                               class="form-control <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               name="answers[<?php echo e($question->id); ?>]"
                                               value="<?php echo e(old('answers.' . $question->id)); ?>"
                                               <?php echo e($question->is_required ? 'required' : ''); ?>>
                                    <?php endif; ?>

                                <?php elseif($question->field_type === 'select' || $question->question === 'Pilih Jenis Pelayanan'): ?>
                                    <?php if($question->question === 'Pilih Jenis Pelayanan'): ?>
                                        <select class="form-select <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="answers[<?php echo e($question->id); ?>]"
                                                <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <option value="">-- Pilih Jenis Pelayanan --</option>
                                            <?php
                                                $services = \App\Models\Service::all();
                                            ?>
                                            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($service->name); ?>"
                                                        <?php echo e(old('answers.' . $question->id) == $service->name ? 'selected' : ''); ?>>
                                                    <?php echo e($service->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    <?php elseif(stripos($question->question, 'usia') !== false || stripos($question->question, 'Umur') !== false): ?>
                                        <select class="form-select <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="answers[<?php echo e($question->id); ?>]"
                                                <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <option value="">-- Pilih Usia --</option>
                                            <option value="Dibawah 20 Tahun" <?php echo e(old('answers.' . $question->id) == 'Dibawah 20 Tahun' ? 'selected' : ''); ?>>Dibawah 20 Tahun</option>
                                            <option value="21 s.d 30 Tahun" <?php echo e(old('answers.' . $question->id) == '21 s.d 30 Tahun' ? 'selected' : ''); ?>>21 s.d 30 Tahun</option>
                                            <option value="31 s.d 40 Tahun" <?php echo e(old('answers.' . $question->id) == '31 s.d 40 Tahun' ? 'selected' : ''); ?>>31 s.d 40 Tahun</option>
                                            <option value="41 s.d 50 Tahun" <?php echo e(old('answers.' . $question->id) == '41 s.d 50 Tahun' ? 'selected' : ''); ?>>41 s.d 50 Tahun</option>
                                            <option value="Diatas 50 Tahun" <?php echo e(old('answers.' . $question->id) == 'Diatas 50 Tahun' ? 'selected' : ''); ?>>Diatas 50 Tahun</option>
                                        </select>
                                    <?php elseif(stripos($question->question, 'pekerjaan') !== false): ?>
                                        <select class="form-select <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="answers[<?php echo e($question->id); ?>]"
                                                <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <option value="">-- Pilih Pekerjaan --</option>
                                            <option value="PNS/TNI/POLRI" <?php echo e(old('answers.' . $question->id) == 'PNS/TNI/POLRI' ? 'selected' : ''); ?>>PNS/TNI/POLRI</option>
                                            <option value="Pegawai Swasta" <?php echo e(old('answers.' . $question->id) == 'Pegawai Swasta' ? 'selected' : ''); ?>>Pegawai Swasta</option>
                                            <option value="Wiraswasta" <?php echo e(old('answers.' . $question->id) == 'Wiraswasta' ? 'selected' : ''); ?>>Wiraswasta</option>
                                            <option value="Petani/Pekebun" <?php echo e(old('answers.' . $question->id) == 'Petani/Pekebun' ? 'selected' : ''); ?>>Petani/Pekebun</option>
                                            <option value="Pelajar/Mahasiswa" <?php echo e(old('answers.' . $question->id) == 'Pelajar/Mahasiswa' ? 'selected' : ''); ?>>Pelajar/Mahasiswa</option>
                                            <option value="Lainnya" <?php echo e(old('answers.' . $question->id) == 'Lainnya' ? 'selected' : ''); ?>>Lainnya</option>
                                        </select>
                                    <?php elseif(stripos($question->question, 'pendidikan') !== false): ?>
                                        <select class="form-select <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="answers[<?php echo e($question->id); ?>]"
                                                <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <option value="">-- Pilih Pendidikan --</option>
                                            <option value="SD" <?php echo e(old('answers.' . $question->id) == 'SD' ? 'selected' : ''); ?>>SD</option>
                                            <option value="SMP" <?php echo e(old('answers.' . $question->id) == 'SMP' ? 'selected' : ''); ?>>SMP</option>
                                            <option value="SMA" <?php echo e(old('answers.' . $question->id) == 'SMA' ? 'selected' : ''); ?>>SMA</option>
                                            <option value="D3" <?php echo e(old('answers.' . $question->id) == 'D3' ? 'selected' : ''); ?>>D3</option>
                                            <option value="D4/S1" <?php echo e(old('answers.' . $question->id) == 'D4/S1' ? 'selected' : ''); ?>>D4/S1</option>
                                            <option value="S2" <?php echo e(old('answers.' . $question->id) == 'S2' ? 'selected' : ''); ?>>S2</option>
                                            <option value="S3" <?php echo e(old('answers.' . $question->id) == 'S3' ? 'selected' : ''); ?>>S3</option>
                                        </select>
                                    <?php else: ?>
                                        <select class="form-select <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                name="answers[<?php echo e($question->id); ?>]"
                                                <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <option value="">-- Pilih <?php echo e($question->question); ?> --</option>
                                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                        <?php echo e(old('answers.' . $question->id) == $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    <?php endif; ?>

                                <?php elseif($question->field_type === 'radio'): ?>
                                    <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check">
                                            <input class="form-check-input <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                   type="radio"
                                                   name="answers[<?php echo e($question->id); ?>]"
                                                   id="q<?php echo e($question->id); ?>_<?php echo e($loop->index); ?>"
                                                   value="<?php echo e($option); ?>"
                                                   <?php echo e(old('answers.' . $question->id) == $option ? 'checked' : ''); ?>

                                                   <?php echo e($question->is_required ? 'required' : ''); ?>>
                                            <label class="form-check-label" for="q<?php echo e($question->id); ?>_<?php echo e($loop->index); ?>">
                                                <?php echo e($option); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php elseif($question->field_type === 'number'): ?>
                                    <input type="number"
                                           class="form-control <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           name="answers[<?php echo e($question->id); ?>]"
                                           value="<?php echo e(old('answers.' . $question->id)); ?>"
                                           <?php echo e($question->is_required ? 'required' : ''); ?>>
                                <?php elseif($question->field_type === 'ticket_number'): ?>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               name="answers[<?php echo e($question->id); ?>]"
                                               placeholder="Masukkan nomor tiket layanan, contoh: LAYANAN-N-2025-001"
                                               value="<?php echo e(old('answers.' . $question->id)); ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="checkTicketBtn">
                                            <i class="fas fa-search"></i> Cek
                                        </button>
                                    </div>
                                    <div id="ticketInfo" class="mt-2"></div>
                                    <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php endif; ?>

                                <?php $__errorArgs = ['answers.' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo e(route('home')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Data Anda aman dan akan dijaga kerahasiaannya
                </small>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        padding: 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkTicketBtn = document.getElementById('checkTicketBtn');
    if (checkTicketBtn) {
        checkTicketBtn.addEventListener('click', function() {
            const ticketInput = document.querySelector('input[name*="[answers]"]');
            const ticketNumber = ticketInput ? ticketInput.value.trim() : '';
            const ticketInfoDiv = document.getElementById('ticketInfo');
            
            if (ticketNumber) {
                // Make AJAX request to check ticket
                fetch('/api/check-ticket/' + ticketNumber)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            if (data.has_survey_completed) {
                                ticketInfoDiv.innerHTML = `
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Tiket ini telah menyelesaikan survei.
                                    </div>
                                `;
                                // Disable the form submission
                                document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = true;
                            } else {
                                ticketInfoDiv.innerHTML = `
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Tiket ditemukan: ${data.ticket.service.name} oleh ${data.ticket.user.name}
                                        <br>
                                        Status: ${data.ticket.status}
                                    </div>
                                `;
                                // Re-enable form submission if it was disabled
                                document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = false;
                            }
                        } else {
                            ticketInfoDiv.innerHTML = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Tiket tidak ditemukan
                                </div>
                            `;
                            // Disable the form submission
                            document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = true;
                        }
                    })
                    .catch(error => {
                        ticketInfoDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error saat memeriksa tiket
                            </div>
                        `;
                    });
            }
        });
    }
    
    // Enable form submission by default
    document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = false;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/survey/form-step1.blade.php ENDPATH**/ ?>