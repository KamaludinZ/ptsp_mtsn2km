<?php $__env->startSection('title', 'Pengaturan Umum'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pengaturan Umum</h1>
        <p class="text-gray-600">Kelola pengaturan aplikasi, branding, kontak, dan tema</p>
    </div>

    <!-- Alert Messages -->
    <?php if(session('success')): ?>
    <div class="alert alert-success shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?php echo e(session('success')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-error shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?php echo e(session('error')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Settings Form -->
    <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <!-- Tabs -->
        <div class="tabs tabs-boxed mb-4">
            <a class="tab tab-active" data-tab="branding">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Branding
            </a>
            <a class="tab" data-tab="contact">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Kontak
            </a>
            <a class="tab" data-tab="social">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                Sosial Media
            </a>
            <a class="tab" data-tab="operational">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Jam Operasional
            </a>
            <a class="tab" data-tab="links">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Link Terkait
            </a>
            <a class="tab" data-tab="theme">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Tema
            </a>
            <a class="tab" data-tab="integration">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                Integrasi
            </a>
        </div>

        <!-- Tab Content -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">

                <!-- Branding Tab -->
                <div id="branding-tab" class="tab-content">
                    <h2 class="card-title text-2xl mb-4">Branding & Identitas</h2>

                    <?php if(isset($settings['branding'])): ?>
                        <?php $__currentLoopData = $settings['branding']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>

                                <?php if($setting->type === 'image'): ?>
                                    <?php if($setting->value): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo e(asset('storage/' . $setting->value)); ?>" alt="<?php echo e($setting->display_name); ?>" class="w-32 h-32 object-contain border rounded">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="<?php echo e($setting->key); ?>" class="file-input file-input-bordered w-full" accept="image/*">
                                <?php elseif($setting->type === 'textarea'): ?>
                                    <textarea name="<?php echo e($setting->key); ?>" class="textarea textarea-bordered h-24" placeholder="<?php echo e($setting->description); ?>"><?php echo e($setting->value); ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <?php endif; ?>

                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Favicon</span>
                        </label>
                        <?php if(isset($settings['branding'])): ?>
                            <?php $favicon = $settings['branding']->where('key', 'app_favicon')->first(); ?>
                            <?php if($favicon && $favicon->value): ?>
                                <div class="mb-2">
                                    <img src="<?php echo e(asset('storage/' . $favicon->value)); ?>" alt="Favicon" class="w-16 h-16 object-contain border rounded">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <input type="file" name="app_favicon" class="file-input file-input-bordered w-full" accept="image/x-icon,image/png,image/svg+xml">
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Favicon aplikasi yang ditampilkan di browser tab (Rekomendasi: 32x32px atau 64x64px, ICO/PNG/SVG).</span>
                        </label>
                    </div>
                </div>

                <!-- Contact Tab -->
                <div id="contact-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Informasi Kontak</h2>

                    <?php if(isset($settings['contact'])): ?>
                        <?php $__currentLoopData = $settings['contact']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <input type="<?php echo e($setting->type); ?>" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>

                <!-- Social Media Tab -->
                <div id="social-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Sosial Media</h2>

                    <?php if(isset($settings['social'])): ?>
                        <?php $__currentLoopData = $settings['social']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <input type="url" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>

                <!-- Operating Hours Tab -->
                <div id="operational-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Jam Operasional</h2>

                    <?php if(isset($settings['operating_hours'])): ?>
                        <?php $__currentLoopData = $settings['operating_hours']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <input type="text" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>

                <!-- Related Links Tab -->
                <div id="links-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Link Terkait</h2>

                    <?php if(isset($settings['related_links'])): ?>
                        <?php $__currentLoopData = $settings['related_links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <input type="url" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                    <?php if(isset($settings['general'])): ?>
                        <h3 class="text-xl font-semibold mt-6 mb-4">Copyright & Info</h3>
                        <?php $__currentLoopData = $settings['general']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <?php if($setting->type === 'textarea'): ?>
                                    <textarea name="<?php echo e($setting->key); ?>" class="textarea textarea-bordered h-24" placeholder="<?php echo e($setting->description); ?>"><?php echo e($setting->value); ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?php echo e($setting->key); ?>" value="<?php echo e($setting->value); ?>" class="input input-bordered w-full" placeholder="<?php echo e($setting->description); ?>">
                                <?php endif; ?>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>

                <!-- Theme Tab -->
                <div id="theme-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Tema Aplikasi</h2>

                    <?php if(isset($settings['theme'])): ?>
                        <?php $__currentLoopData = $settings['theme']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold"><?php echo e($setting->display_name); ?></span>
                                </label>
                                <select name="<?php echo e($setting->key); ?>" class="select select-bordered w-full">
                                    <?php $__currentLoopData = $themes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($theme); ?>" <?php echo e($setting->value === $theme ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($theme)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500"><?php echo e($setting->description); ?></span>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                    <!-- Theme Preview -->
                    <div class="alert alert-info mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Tema akan diterapkan setelah menyimpan dan refresh halaman.</span>
                    </div>
                </div>

                <!-- Integration Tab -->
                <div id="integration-tab" class="tab-content hidden">
                    <h2 class="card-title text-2xl mb-4">Pengaturan Integrasi</h2>

                    <!-- WhatsApp Settings -->
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-2">Pengaturan WhatsApp Gateway</h3>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">WhatsApp API URL</span>
                                <div class="tooltip" data-tip="URL endpoint dari penyedia API WhatsApp.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="whatsapp_api_url" value="<?php echo e($settings['integration']->where('key', 'whatsapp_api_url')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="https://api.whatsapp.com/send">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">WhatsApp API Token</span>
                                <div class="tooltip" data-tip="Token otentikasi untuk mengakses API.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="password" name="whatsapp_api_token" value="<?php echo e($settings['integration']->where('key', 'whatsapp_api_token')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="Token rahasia API">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">WhatsApp Sender ID</span>
                                <div class="tooltip" data-tip="Nomor WhatsApp yang terdaftar sebagai pengirim.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="whatsapp_sender_id" value="<?php echo e($settings['integration']->where('key', 'whatsapp_sender_id')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="Nomor pengirim (misal: 6281234567890)">
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Pengaturan Email (SMTP)</h3>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Mailer</span>
                                <div class="tooltip" data-tip="Contoh: smtp, log, array.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_mailer" value="<?php echo e($settings['integration']->where('key', 'mail_mailer')->first()->value ?? 'smtp'); ?>" class="input input-bordered w-full" placeholder="smtp">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Host</span>
                                <div class="tooltip" data-tip="Host server SMTP.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_host" value="<?php echo e($settings['integration']->where('key', 'mail_host')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="smtp.mailgun.org">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Port</span>
                                <div class="tooltip" data-tip="Port server SMTP.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_port" value="<?php echo e($settings['integration']->where('key', 'mail_port')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="587">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Username</span>
                                <div class="tooltip" data-tip="Username untuk otentikasi SMTP.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_username" value="<?php echo e($settings['integration']->where('key', 'mail_username')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="Username SMTP">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Password</span>
                                <div class="tooltip" data-tip="Password untuk otentikasi SMTP.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="password" name="mail_password" value="<?php echo e($settings['integration']->where('key', 'mail_password')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="Password SMTP">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Enkripsi</span>
                                <div class="tooltip" data-tip="Contoh: tls, ssl.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_encryption" value="<?php echo e($settings['integration']->where('key', 'mail_encryption')->first()->value ?? 'tls'); ?>" class="input input-bordered w-full" placeholder="tls">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Alamat Pengirim</span>
                                <div class="tooltip" data-tip="Alamat email yang akan digunakan sebagai pengirim.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="email" name="mail_from_address" value="<?php echo e($settings['integration']->where('key', 'mail_from_address')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="noreply@example.com">
                        </div>
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Nama Pengirim</span>
                                <div class="tooltip" data-tip="Nama yang akan ditampilkan sebagai pengirim.">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </label>
                            <input type="text" name="mail_from_name" value="<?php echo e($settings['integration']->where('key', 'mail_from_name')->first()->value ?? ''); ?>" class="input input-bordered w-full" placeholder="Nama Aplikasi">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="card-actions justify-end px-6 pb-6">
                <button type="button" onclick="window.location.reload()" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('tab-active'));

                // Add active class to clicked tab
                this.classList.add('tab-active');

                // Hide all tab contents
                tabContents.forEach(content => content.classList.add('hidden'));

                // Show selected tab content
                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-tab').classList.remove('hidden');
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>