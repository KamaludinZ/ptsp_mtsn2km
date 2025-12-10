<?php $__env->startSection('title', 'Survey Kepuasan Masyarakat - SKM'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Survey Kepuasan Masyarakat (SKM)
                </h2>
                <p class="text-muted">MTsN 2 Kota Malang</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 2 dari 3</span>
                    <span class="text-muted small">66%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 66%;"
                         aria-valuenow="66" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Tahap 2: Survei Kepuasan Masyarakat (SKM)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Petunjuk:</strong> Berikan penilaian Anda terhadap kualitas pelayanan di MTsN 2 Kota Malang.
                        Terdapat <strong>9 pertanyaan</strong> dengan 4 pilihan jawaban.
                    </div>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

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

                    <form action="<?php echo e(route('survey.step2.store')); ?>" method="POST" id="step2Form">
                        <?php echo csrf_field(); ?>

                        <!-- 9 Survei Kepuasan Masyarakat (SKM) Questions -->
                        
                        <!-- Question 1 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">1</span>
                                Bagaimana pendapat Saudara tentang kesesuaian persyaratan layanan di MTsN 2 Kota Malang dengan jenis pelayanannya?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.7'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_1"
                                               value="Tidak Sesuai"
                                               <?php echo e(old('answers.7') == 'Tidak Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_1">
                                            Tidak Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.7'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_2"
                                               value="Kurang Sesuai"
                                               <?php echo e(old('answers.7') == 'Kurang Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_2">
                                            Kurang Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.7'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_3"
                                               value="Sesuai"
                                               <?php echo e(old('answers.7') == 'Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_3">
                                            Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.7'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_4"
                                               value="Sangat Sesuai"
                                               <?php echo e(old('answers.7') == 'Sangat Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_4">
                                            Sangat Sesuai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.7'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 2 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">2</span>
                                Bagaimana pendapat Saudara tentang kemudahan prosedur pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.8'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_1"
                                               value="Tidak Mudah"
                                               <?php echo e(old('answers.8') == 'Tidak Mudah' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_1">
                                            Tidak Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.8'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_2"
                                               value="Kurang Mudah"
                                               <?php echo e(old('answers.8') == 'Kurang Mudah' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_2">
                                            Kurang Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.8'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_3"
                                               value="Mudah"
                                               <?php echo e(old('answers.8') == 'Mudah' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_3">
                                            Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.8'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_4"
                                               value="Sangat Mudah"
                                               <?php echo e(old('answers.8') == 'Sangat Mudah' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_4">
                                            Sangat Mudah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.8'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 3 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">3</span>
                                Bagaimana pendapat Saudara tentang kecepatan pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.9'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_1"
                                               value="Tidak Cepat"
                                               <?php echo e(old('answers.9') == 'Tidak Cepat' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_1">
                                            Tidak Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.9'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_2"
                                               value="Kurang Cepat"
                                               <?php echo e(old('answers.9') == 'Kurang Cepat' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_2">
                                            Kurang Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.9'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_3"
                                               value="Cepat"
                                               <?php echo e(old('answers.9') == 'Cepat' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_3">
                                            Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.9'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_4"
                                               value="Sangat Cepat"
                                               <?php echo e(old('answers.9') == 'Sangat Cepat' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_4">
                                            Sangat Cepat
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.9'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 4 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">4</span>
                                Bagaimana pendapat Saudara tentang Jenis pelayanan ini di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_1"
                                               value="Tidak Bagus"
                                               <?php echo e(old('answers.10') == 'Tidak Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_1">
                                            Tidak Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_2"
                                               value="Kurang Bagus"
                                               <?php echo e(old('answers.10') == 'Kurang Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_2">
                                            Kurang Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_3"
                                               value="Bagus"
                                               <?php echo e(old('answers.10') == 'Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_3">
                                            Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_4"
                                               value="Sangat Bagus"
                                               <?php echo e(old('answers.10') == 'Sangat Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_4">
                                            Sangat Bagus
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 5 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">5</span>
                                Bagaimana pendapat Saudara tentang kemampuan petugas dalam memberikan pelayanan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.11'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_1"
                                               value="Tidak Mampu"
                                               <?php echo e(old('answers.11') == 'Tidak Mampu' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_1">
                                            Tidak Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.11'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_2"
                                               value="Kurang Mampu"
                                               <?php echo e(old('answers.11') == 'Kurang Mampu' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_2">
                                            Kurang Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.11'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_3"
                                               value="Mampu"
                                               <?php echo e(old('answers.11') == 'Mampu' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_3">
                                            Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.11'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_4"
                                               value="Sangat Mampu"
                                               <?php echo e(old('answers.11') == 'Sangat Mampu' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_4">
                                            Sangat Mampu
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.11'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 6 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">6</span>
                                Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas dalam memberikan pelayanan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.12'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_1"
                                               value="Tidak Sopan"
                                               <?php echo e(old('answers.12') == 'Tidak Sopan' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_1">
                                            Tidak Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.12'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_2"
                                               value="Kurang Sopan"
                                               <?php echo e(old('answers.12') == 'Kurang Sopan' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_2">
                                            Kurang Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.12'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_3"
                                               value="Sopan"
                                               <?php echo e(old('answers.12') == 'Sopan' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_3">
                                            Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.12'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_4"
                                               value="Sangat Sopan"
                                               <?php echo e(old('answers.12') == 'Sangat Sopan' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_4">
                                            Sangat Sopan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.12'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 7 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">7</span>
                                Bagaimana pendapat Saudara tentang maklumat pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.13'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_1"
                                               value="Tidak Jelas"
                                               <?php echo e(old('answers.13') == 'Tidak Jelas' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_1">
                                            Tidak Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.13'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_2"
                                               value="Kurang Jelas"
                                               <?php echo e(old('answers.13') == 'Kurang Jelas' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_2">
                                            Kurang Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.13'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_3"
                                               value="Jelas"
                                               <?php echo e(old('answers.13') == 'Jelas' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_3">
                                            Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.13'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_4"
                                               value="Sangat Jelas"
                                               <?php echo e(old('answers.13') == 'Sangat Jelas' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_4">
                                            Sangat Jelas
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.13'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 8 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">8</span>
                                Bagaimana pendapat Saudara tentang penanganan pengaduan, saran dan masukan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.14'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_1"
                                               value="Tidak Bagus"
                                               <?php echo e(old('answers.14') == 'Tidak Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_1">
                                            Tidak Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.14'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_2"
                                               value="Kurang Bagus"
                                               <?php echo e(old('answers.14') == 'Kurang Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_2">
                                            Kurang Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.14'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_3"
                                               value="Bagus"
                                               <?php echo e(old('answers.14') == 'Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_3">
                                            Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.14'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_4"
                                               value="Sangat Bagus"
                                               <?php echo e(old('answers.14') == 'Sangat Bagus' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_4">
                                            Sangat Bagus
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.14'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Question 9 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">9</span>
                                Bagaimana pendapat Saudara tentang kesesuaian biaya pelayanan dengan standar pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.15'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_1"
                                               value="Selalu Tidak Sesuai"
                                               <?php echo e(old('answers.15') == 'Selalu Tidak Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_1">
                                            Selalu Tidak Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.15'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_2"
                                               value="Kadang-kadang Sesuai"
                                               <?php echo e(old('answers.15') == 'Kadang-kadang Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_2">
                                            Kadang-kadang Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.15'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_3"
                                               value="Sesuai"
                                               <?php echo e(old('answers.15') == 'Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_3">
                                            Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input <?php $__errorArgs = ['answers.15'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_4"
                                               value="Selalu Sesuai"
                                               <?php echo e(old('answers.15') == 'Selalu Sesuai' ? 'checked' : ''); ?>

                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_4">
                                            Selalu Sesuai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <?php $__errorArgs = ['answers.15'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo e(route('survey.form')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-success">
                                Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
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
    .question-card {
        background: #f8f9fa;
        border: 2px solid #e5e7eb !important;
        border-radius: 12px !important;
        transition: all 0.3s ease;
    }
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .form-check-card label {
        background: white;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .form-check-card label:hover {
        background: #f1f5f9;
        border-color: #3b82f6;
    }
    .form-check-input:checked + label {
        background: #dbeafe !important;
        border-color: #3b82f6 !important;
        font-weight: 600;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\survey\form-step2.blade.php ENDPATH**/ ?>