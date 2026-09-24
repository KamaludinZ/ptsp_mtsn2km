<?php $__env->startSection('title', 'Daftar Akun - ' . config('app.name')); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .register-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 40px 16px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    }

    [data-theme="dark"] .register-container {
        background: linear-gradient(135deg, #1a2e1a 0%, #0f1e0f 50%, #071207 100%);
    }

    /* Dark Mode Toggle Button */
    .theme-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s;
        z-index: 1000;
    }

    .theme-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .register-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Desktop Layout - Header di sebelah kiri */
    @media (min-width: 768px) {
        .register-card {
            flex-direction: row;
            min-height: 650px;
        }
    }

    [data-theme="dark"] .register-card {
        background: #111827;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }

    .register-header {
        background: var(--bs-primary);
        color: white;
        padding: 40px 32px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Desktop - Header occupy 35% width */
    @media (min-width: 768px) {
        .register-header {
            width: 35%;
            min-height: 650px;
            padding: 60px 40px;
        }
    }

    .register-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
    }

    /* Desktop - Larger icon */
    @media (min-width: 768px) {
        .register-icon {
            width: 100px;
            height: 100px;
            font-size: 50px;
            margin-bottom: 24px;
        }
    }

    .register-header h1 {
        font-size: 24px;
        margin-bottom: 12px;
    }

    @media (min-width: 768px) {
        .register-header h1 {
            font-size: 28px;
        }
    }

    .register-features {
        margin-top: 32px;
        text-align: left;
    }

    .register-features .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        opacity: 0.95;
    }

    .register-features .feature-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .register-features .feature-text {
        font-size: 14px;
        line-height: 1.4;
    }

    .register-form-wrapper {
        flex: 1;
        background: #ffffff;
    }

    [data-theme="dark"] .register-form-wrapper {
        background: #111827;
    }

    /* Desktop - Form wrapper occupy 65% width */
    @media (min-width: 768px) {
        .register-form-wrapper {
            width: 65%;
            overflow-y: auto;
        }
    }

    .tab-buttons {
        display: flex;
        border-bottom: 2px solid var(--bs-border-color);
    }

    .tab-button {
        flex: 1;
        padding: 16px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--bs-secondary-text);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .tab-button:hover {
        background: rgba(20, 83, 45, 0.05);
        color: var(--bs-primary);
    }

    .tab-button.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: rgba(20, 83, 45, 0.05);
    }

    .tab-content {
        display: none;
        padding: 24px;
        animation: fadeIn 0.3s;
    }

    @media (min-width: 768px) {
        .tab-content {
            padding: 32px 40px;
        }
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--bs-text);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        background: #ffffff;
        color: var(--bs-text);
        transition: all 0.2s;
    }

    [data-theme="dark"] .form-control {
        background: #1f2937;
        border-color: #374151;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(20, 83, 45, 0.1);
    }

    .btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--bs-primary);
        color: #ffffff !important;
        width: 100%;
    }

    .btn-primary:hover {
        background: var(--bs-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary);
    }

    .btn-outline:hover {
        background: var(--bs-primary);
        color: #ffffff;
    }

    .info-box {
        background: rgba(59, 130, 246, 0.1);
        border-left: 4px solid #3b82f6;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    [data-theme="dark"] .info-box {
        background: rgba(59, 130, 246, 0.2);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .alert-danger {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
    }

    .bottom-links {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--bs-border-color);
    }

    @media (min-width: 576px) {
        .bottom-links {
            flex-direction: row;
        }
    }

    .bottom-links a, .bottom-links button {
        flex: 1;
        text-align: center;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-11 col-lg-10 col-xl-9">
                <div class="register-card">
                    <!-- Header -->
                    <div class="register-header">
                        <div>
                            <div class="register-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h1 class="fw-bold mb-2">Daftar Akun Baru</h1>
                            <p class="mb-0" style="opacity: 0.9;">Bergabung dengan Sistem PTSP MTsN 2 Kota Malang</p>

                            <!-- Features - Hidden on mobile, shown on desktop -->
                            <div class="register-features d-none d-md-block">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="feature-text">Gratis dan mudah</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="feature-text">Akses layanan online</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="feature-text">Tracking permohonan</div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="feature-text">Notifikasi real-time</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Wrapper -->
                    <div class="register-form-wrapper">
                        <!-- Tabs -->
                        <div class="tab-buttons">
                        <button class="tab-button active" onclick="switchTab('umum')">
                            <i class="fas fa-user me-2"></i>Pendaftar Umum
                        </button>
                        <button class="tab-button" onclick="switchTab('civitas')">
                            <i class="fas fa-id-card me-2"></i>Civitas Internal
                        </button>
                    </div>

                    <!-- Tab Content: Pendaftar Umum -->
                    <div id="tab-umum" class="tab-content active">
                        <div class="info-box">
                            <p class="mb-0" style="color: #1e40af; font-size: 14px;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Pendaftar Umum:</strong> Untuk masyarakat umum yang ingin mengakses layanan PTSP MTsN 2 Kota Malang.
                            </p>
                        </div>

                        <form method="POST" action="<?php echo e(route('register')); ?>" id="form-umum">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_type" value="umum">

                            <div class="form-group">
                                <label for="name_umum" class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name_umum" name="name"
                                       class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('name')); ?>"
                                       placeholder="Masukkan nama lengkap Anda"
                                       required autofocus>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email_umum" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" id="email_umum" name="email"
                                               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               value="<?php echo e(old('email')); ?>"
                                               placeholder="contoh@email.com"
                                               required>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="whatsapp_number_umum" class="form-label">
                                            Nomor WhatsApp <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="whatsapp_number_umum" name="whatsapp_number"
                                               class="form-control <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               value="<?php echo e(old('whatsapp_number')); ?>"
                                               placeholder="Contoh: 081234567890"
                                               required>
                                        <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_umum" class="form-label">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" id="password_umum" name="password"
                                               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               placeholder="Min. 8 karakter"
                                               required>
                                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation_umum" class="form-label">
                                            Konfirmasi Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" id="password_confirmation_umum" name="password_confirmation"
                                               class="form-control"
                                               placeholder="Ulangi password"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Content: Civitas Internal -->
                    <div id="tab-civitas" class="tab-content">
                        <div class="info-box">
                            <p class="mb-0" style="color: #1e40af; font-size: 14px;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Civitas Internal:</strong> Untuk Siswa, Guru, dan Pegawai MTsN 2 Kota Malang. Memerlukan kode registrasi khusus.
                            </p>
                        </div>

                        <form method="POST" action="<?php echo e(route('register')); ?>" id="form-civitas">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="is_civitas" value="1">

                            <div class="form-group">
                                <label for="registration_code" class="form-label">
                                    Kode Registrasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="registration_code" name="registration_code"
                                       class="form-control <?php $__errorArgs = ['registration_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('registration_code')); ?>"
                                       placeholder="Masukkan kode 10 digit"
                                       maxlength="10"
                                       pattern="[0-9]{10}"
                                       required>
                                <small class="text-muted">Kode registrasi 10 digit angka yang diberikan oleh admin</small>
                                <?php $__errorArgs = ['registration_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="form-group">
                                <label for="name_civitas" class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name_civitas" name="name"
                                       class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('name')); ?>"
                                       placeholder="Masukkan nama lengkap Anda"
                                       required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email_civitas" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" id="email_civitas" name="email"
                                               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               value="<?php echo e(old('email')); ?>"
                                               placeholder="contoh@email.com"
                                               required>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="whatsapp_number_civitas" class="form-label">
                                            Nomor WhatsApp <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="whatsapp_number_civitas" name="whatsapp_number"
                                               class="form-control <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               value="<?php echo e(old('whatsapp_number')); ?>"
                                               placeholder="Contoh: 081234567890"
                                               required>
                                        <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_civitas" class="form-label">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" id="password_civitas" name="password"
                                               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               placeholder="Min. 8 karakter"
                                               required>
                                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger mt-1" style="font-size: 14px;"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation_civitas" class="form-label">
                                            Konfirmasi Password <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" id="password_confirmation_civitas" name="password_confirmation"
                                               class="form-control"
                                               placeholder="Ulangi password"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-plus me-2"></i>Daftar Sebagai Civitas
                                </button>
                            </div>
                        </form>
                    </div>

                        <!-- Bottom Links -->
                        <div class="px-4 pb-4">
                            <div class="bottom-links">
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-outline d-flex align-items-center justify-content-center">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sudah Punya Akun? Login
                                </a>
                                <a href="<?php echo e(url('/')); ?>" class="btn btn-outline d-flex align-items-center justify-content-center">
                                    <i class="fas fa-home me-2"></i>Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End Form Wrapper -->
                </div>
                <!-- End Register Card -->

                <!-- Email Verification Notice -->
                <div class="text-center mt-4 p-3" style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; backdrop-filter: blur(10px);">
                    <p class="mb-0" style="color: var(--bs-text); font-size: 14px;">
                        <i class="fas fa-envelope-circle-check me-2"></i>
                        Setelah mendaftar, Anda akan menerima email verifikasi. Silakan verifikasi email Anda untuk dapat mengakses dashboard.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function switchTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Remove active from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab
    document.getElementById('tab-' + tab).classList.add('active');

    // Mark button as active
    event.target.closest('.tab-button').classList.add('active');
}

// Update theme icon based on current theme
function updateThemeIcon() {
    const theme = document.documentElement.getAttribute('data-theme');
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', updateThemeIcon);

// Override toggleTheme to update icon
const originalToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
    originalToggleTheme();
    updateThemeIcon();
};
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/auth/register.blade.php ENDPATH**/ ?>