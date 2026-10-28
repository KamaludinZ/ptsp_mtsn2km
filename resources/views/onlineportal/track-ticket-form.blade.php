@extends('layouts.public')

@section('title', 'Lacak Tiket Layanan')

@push('styles')
<style>
    /* Service Component Hover Effect */
    .service-component {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.25) !important;
        backdrop-filter: blur(10px);
    }

    .service-component:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        background: rgba(255, 255, 255, 0.35) !important;
    }

    .icon-hover {
        transition: transform 0.3s ease;
    }

    .service-component:hover .icon-hover {
        transform: scale(1.2) rotate(5deg);
    }

    /* Dark mode support for stat cards */
    [data-theme="dark"] .stat-card {
        background-color: var(--bs-surface) !important;
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] .stat-card .card {
        background-color: var(--bs-surface) !important;
    }

    /* Dark mode for additional stats */
    [data-theme="dark"] .stats-additional {
        background-color: var(--bs-surface) !important;
        border-color: var(--bs-primary) !important;
    }

    /* Dark mode for performance section */
    [data-theme="dark"] .performance-section {
        background-color: var(--bs-surface) !important;
    }

    /* Komponen text always white on green gradient */
    .component-text {
        color: white !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Dark mode specific fixes */
    [data-theme="dark"] .text-muted {
        color: #d1d5db !important;
    }

    [data-theme="dark"] .lead {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] h1,
    [data-theme="dark"] h2,
    [data-theme="dark"] h3,
    [data-theme="dark"] h4,
    [data-theme="dark"] h5,
    [data-theme="dark"] h6 {
        color: var(--bs-text) !important;
    }

    [data-theme="dark"] p {
        color: var(--bs-text);
    }

    /* Tracking-specific styles */
    .tracking-input-group {
        display: flex;
        border: 2px solid var(--bs-gray-300);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s;
    }

    .tracking-input-group:focus-within {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(20, 83, 45, 0.15);
    }

    .tracking-input {
        flex: 1;
        border: none;
        padding: 1rem 1.25rem;
        font-size: 1.1rem;
        outline: none;
    }

    .tracking-button {
        background-color: var(--bs-primary);
        color: white;
        border: none;
        padding: 0 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .tracking-button:hover {
        background-color: var(--bs-primary-dark);
    }

    .tracking-button:focus-visible {
        outline: 3px solid #1d4ed8;
        outline-offset: -3px;
    }

    @media (max-width: 575.98px) {
        .tracking-input-group { flex-direction: column; }
        .tracking-input { width: 100%; font-size: 1rem; }
        .tracking-button { padding: 0.875rem 1rem; }
    }

    .tracking-card {
        background-color: var(--bs-bg);
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--bs-gray-200);
    }

    .process-table {
        width: 100%;
        border-collapse: collapse;
    }

    .process-table th,
    .process-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--bs-gray-200);
    }

    .process-table th {
        background-color: rgba(20, 83, 45, 0.05);
        font-weight: 600;
        color: var(--bs-primary);
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: #92400e;
    }

    .status-processing {
        background-color: rgba(2, 132, 199, 0.1);
        color: #1e40af;
    }

    .status-completed {
        background-color: rgba(16, 185, 129, 0.1);
        color: #065f46;
    }

    .action-button {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .action-button-primary {
        background-color: var(--bs-primary);
        color: white;
    }

    .action-button-primary:hover {
        background-color: var(--bs-primary-dark);
    }

    .action-button-outline {
        background-color: transparent;
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }

    .action-button-outline:hover {
        background-color: var(--bs-primary);
        color: white;
    }

    .result-section {
        margin-top: 2rem;
        display: none;
    }

    .result-header {
        border-bottom: 2px solid var(--bs-gray-200);
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .result-content {
        padding: 1.5rem;
        border-radius: 0.75rem;
        background-color: rgba(20, 83, 45, 0.03);
    }

    .loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { -webkit-transform: rotate(360deg); }
    }
    @-webkit-keyframes spin {
        to { -webkit-transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Lacak <span style="color: var(--bs-primary);">Status Tiket</span>
        </h1>
        <p class="lead text-muted">
            Masukkan nomor tiket Anda untuk melihat progres layanan secara real-time.
        </p>
    </div>

    <!-- Tracking Form Section -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="tracking-card p-4 p-md-5">
                <form id="trackingForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="ticket_number" class="form-label fw-bold mb-2">Nomor Tiket</label>
                        <div class="tracking-input-group">
                            <input type="text" 
                                   class="tracking-input" 
                                   id="ticket_number" 
                                   name="ticket_number" 
                                   placeholder="Contoh: PTSP-202510-0001"
                                   autocomplete="off"
                                   inputmode="text" 
                                   required>
                            <button type="submit" class="tracking-button">
                                <i class="fas fa-search me-2"></i>Lacak
                            </button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="track_email" class="form-label fw-semibold mb-1">Email pemohon <span class="text-muted fw-normal small">(opsional)</span></label>
                        <input type="email" class="form-control" id="track_email" name="email" autocomplete="email"
                               placeholder="Email yang dipakai saat mengajukan">
                        <div class="form-text">Isi untuk melihat keterangan permohonan dan catatan petugas. Tanpa email, hanya status dan tahapan yang ditampilkan.</div>
                    </div>
                </form>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memproses...</span>
                    </div>
                    <p class="mt-2 text-muted">Memproses permintaan Anda...</p>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorText"></span>
                </div>

                <!-- Tracking Result Section -->
                <div id="trackingResult" class="result-section">
                    <div class="result-header">
                        <h3 class="h4 fw-bold">Detail Tiket</h3>
                        <p class="text-muted">Informasi terkini tentang permohonan layanan Anda</p>
                    </div>

                    <div class="result-content">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nomor Tiket:</strong></p>
                                <p class="text-muted mb-0" id="ticketNumber">-</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Status:</strong></p>
                                <p class="mb-0"><x-status-badge id="statusBadge" status="" /></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nama Layanan:</strong></p>
                                <p class="text-muted mb-0" id="serviceName">-</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Jenis Pelayanan:</strong></p>
                                <p class="text-muted mb-0" id="serviceMode">-</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tanggal Pengajuan:</strong></p>
                                <p class="text-muted mb-0" id="submittedDate">-</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Estimasi Selesai:</strong></p>
                                <p class="text-muted mb-0" id="estimatedDate">-</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="mb-1"><strong>Deskripsi:</strong></p>
                            <p class="text-muted mb-0" id="description">-</p>
                        </div>

                        <!-- Process Information Table -->
                        <div class="table-responsive">
                            <table class="process-table">
                                <thead>
                                    <tr>
                                        <th>Langkah</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="processTableBody">
                                    <!-- Process steps will be populated here -->
                                </tbody>
                            </table>
                        </div>

                        {{--
                            Hasil layanan of a finished request (filled by showOutput()):
                            a file to download, by signing in to the applicant's account or,
                            without one, by proving the e-mail or WhatsApp number given on the
                            request; or the product to collect at the counter.
                        --}}
                        <section class="mt-4" id="outputPanel" aria-labelledby="outputHeading" hidden>
                            <h3 class="h5 fw-bold mb-3" id="outputHeading"><i class="fas fa-box-open me-2 text-primary" aria-hidden="true"></i>Hasil layanan</h3>

                            <div id="outputDownload" hidden>
                                <p class="text-muted mb-3">Permohonan selesai dan hasil layanannya berupa berkas yang dapat diunduh.</p>
                                <div class="row g-3">
                                    <div class="col-md-6" id="outputSignIn" hidden>
                                        <div class="border rounded-3 p-3 h-100">
                                            <h4 class="h6 fw-bold"><i class="fas fa-user-check me-2 text-primary" aria-hidden="true"></i>Punya akun pemohon?</h4>
                                            <p class="small text-muted">Masuk ke akun yang mengajukan permohonan ini, lalu unduh tanpa verifikasi tambahan.</p>
                                            <a href="#" class="action-button action-button-primary w-100" id="outputSignInLink">
                                                <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Masuk untuk mengunduh
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="outputVerify" hidden>
                                        <div class="border rounded-3 p-3 h-100">
                                            <h4 class="h6 fw-bold"><i class="fas fa-shield-alt me-2 text-primary" aria-hidden="true"></i>Tanpa akun</h4>
                                            <p class="small text-muted">Tuliskan email atau nomor WhatsApp yang Anda berikan saat mengajukan permohonan.</p>
                                            <button type="button" class="action-button action-button-outline w-100" id="outputVerifyButton">
                                                <i class="fas fa-download me-2" aria-hidden="true"></i>Verifikasi lalu unduh
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="outputNotYet" class="alert alert-light border mb-0" hidden>
                                <i class="fas fa-hourglass-half me-2" aria-hidden="true"></i>Permohonan masih diproses. Bila hasil layanannya berupa berkas, berkas itu dapat diunduh di sini setelah permohonan selesai.
                            </div>

                            <div id="outputNone" class="alert alert-secondary mb-0" hidden>
                                <i class="fas fa-ban me-2" aria-hidden="true"></i><span id="outputNoneText"></span>
                            </div>

                            <div id="outputPickup" class="alert alert-info mb-0" hidden>
                                <i class="fas fa-store me-2" aria-hidden="true"></i><span id="outputPickupText">Hasil layanan dapat diambil di Ruang PTSP pada jam layanan.</span>
                            </div>
                        </section>

                        {{--
                            Verifikasi sebelum unduh, for an applicant without an account: the
                            e-mail or WhatsApp number given on the request. A few wrong tries
                            lock it for a while (the server counts them).
                        --}}
                        <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form class="modal-content" id="verifyForm" novalidate>
                                    <div class="modal-header">
                                        <h2 class="modal-title h5" id="verifyModalTitle"><i class="fas fa-shield-alt me-2 text-primary" aria-hidden="true"></i>Verifikasi pemohon</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-3">Hasil layanan tiket <strong id="verifyTicket"></strong> hanya untuk pemohonnya.</p>
                                        <label for="verify_contact" class="form-label fw-semibold">Email atau nomor WhatsApp saat mengajukan *</label>
                                        <input type="text" class="form-control form-control-lg" id="verify_contact" name="kontak" autocomplete="off" maxlength="255" required
                                               aria-describedby="verify_contact_help verify_contact_error">
                                        <div id="verify_contact_help" class="form-text">Tulis salah satunya, persis seperti saat mengajukan permohonan.</div>
                                        <div id="verify_contact_error" class="invalid-feedback d-block" role="alert"></div>
                                        <p id="verifyAttempts" class="small text-muted mt-2 mb-0" aria-live="polite" hidden></p>
                                        <div id="verifyLock" class="alert alert-warning mt-3 mb-0" role="status" hidden>
                                            <i class="fas fa-lock me-2" aria-hidden="true"></i>Verifikasi dikunci sementara. Coba lagi dalam <strong id="verifyCountdown">--:--</strong>,
                                            atau masuk ke akun pemohon / ambil di Ruang PTSP.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary" id="verifySubmit"><i class="fas fa-download me-2" aria-hidden="true"></i>Verifikasi &amp; unduh</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('trackingForm');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const trackingResult = document.getElementById('trackingResult');
        const processTableBody = document.getElementById('processTableBody');

        // Initialize the form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ticketNumber = document.getElementById('ticket_number').value;
            if (!ticketNumber.trim()) {
                showError('Silakan masukkan nomor tiket');
                return;
            }
            
            // Show loading indicator
            loadingIndicator.style.display = 'block';
            errorMessage.style.display = 'none';
            trackingResult.style.display = 'none';
            
            // Make AJAX request
            fetch('{{ route("onlineportal.track.ticket.result") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ticket_number: ticketNumber,
                    email: document.getElementById('track_email').value.trim() || null
                })
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                
                if (data.message && !data.ticket_number) {
                    showError(data.message);
                    return;
                }
                
                if (data.ticket_number) {
                    // Hide error and show results
                    errorMessage.style.display = 'none';
                    
                    // Populate the result data
                    document.getElementById('ticketNumber').textContent = data.ticket_number;
                    document.getElementById('serviceName').textContent = data.service?.name || 'Layanan Tidak Ditemukan';
                    document.getElementById('serviceMode').textContent = data.service?.mode || 'Tidak Diketahui';
                    document.getElementById('submittedDate').textContent = data.submitted_at ? new Date(data.submitted_at).toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';
                    
                    // Calculate estimated date based on service processing time
                    let estimatedDate = 'Tidak Tersedia';
                    if (data.service?.processing_time) {
                        // Simple calculation - you might want to adjust this based on your needs
                        estimatedDate = data.service.processing_time;
                    }
                    document.getElementById('estimatedDate').textContent = estimatedDate;
                    document.getElementById('description').textContent = data.verified_owner
                        ? (data.description || '-')
                        : 'Isi email pemohon untuk melihat keterangan permohonan.';
                    
                    // Update status badge
                    const statusBadge = document.getElementById('statusBadge');
                    statusBadge.querySelector('[data-status-label]').textContent = data.status_label || data.status;
                    statusBadge.dataset.status = data.status;
                    statusBadge.className = 'status-pill status-pill--md status-pill--' + (data.status_color || 'gray');

                    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

                    // Populate process table
                    processTableBody.innerHTML = '';
                    
                    // Add ticket creation as first step
                    const creationRow = document.createElement('tr');
                    creationRow.innerHTML = `
                        <td>Pembuatan Tiket</td>
                        <td><span class="status-badge status-completed">Selesai</span></td>
                        <td>${data.submitted_at ? new Date(data.submitted_at).toLocaleDateString('id-ID') : '-'}</td>
                        <td>Permohonan dibuat dan tiket diterbitkan</td>
                    `;
                    processTableBody.appendChild(creationRow);
                    
                    // Add workflow steps if available
                    if (data.workflow_steps && Array.isArray(data.workflow_steps)) {
                        data.workflow_steps.forEach(step => {
                            const row = document.createElement('tr');
                            const completedAt = step.pivot?.completed_at ? new Date(step.pivot.completed_at).toLocaleDateString('id-ID') : '-';
                            const status = step.pivot?.completed_at ? 'Selesai' : 'Dalam Proses';
                            const statusClass = step.pivot?.completed_at ? 'status-completed' : 'status-processing';
                            
                            row.innerHTML = `
                                <td>${escapeHtml(step.name || 'Langkah Tidak Dikenal')}</td>
                                <td><span class="status-badge ${statusClass}">${status}</span></td>
                                <td>${completedAt}</td>
                                <td>${escapeHtml(step.pivot?.notes || 'Tidak ada catatan')}</td>
                            `;
                            processTableBody.appendChild(row);
                        });
                    }
                    
                    // Handle status logs if available
                    if (data.logs && Array.isArray(data.logs)) {
                        data.logs.forEach(log => {
                            if (log.action && log.action !== 'created') { // Skip creation as it's added separately
                                const row = document.createElement('tr');
                                const logDate = log.created_at ? new Date(log.created_at).toLocaleDateString('id-ID') : '-';
                                
                                row.innerHTML = `
                                    <td>${escapeHtml(log.action_label || log.action || 'Aktivitas')}</td>
                                    <td><span class="status-badge status-completed">Terupdate</span></td>
                                    <td>${logDate}</td>
                                    <td>${escapeHtml(log.notes || (data.verified_owner ? 'Tidak ada catatan' : '–'))}</td>
                                `;
                                processTableBody.appendChild(row);
                            }
                        });
                    }
                    
                    trackedTicket = data.ticket_number;
                    showOutput(data);

                    // Show the result section
                    trackingResult.style.display = 'block';
                } else {
                    showError('Terjadi kesalahan saat mengambil data');
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                console.error('Error:', error);
                showError('Terjadi kesalahan jaringan. Silakan coba lagi.');
            });
        });
        
        // The "Hasil layanan" panel, by the request's status: a download (or pickup) once it is
        // finished, a word on when otherwise. data.hasil (berkas, akun, masuk_url) tells how the
        // file is reached; until the server sends it, a file is assumed reachable both ways.
        function showOutput(data) {
            const output = data.hasil || { berkas: !!data.has_output_file, akun: null, masuk_url: '{{ url('/portal/permohonan') }}' };
            const done = data.status === 'completed';
            const closed = { rejected: 'Permohonan ditolak, sehingga tidak ada hasil layanan.', cancelled: 'Permohonan dibatalkan, sehingga tidak ada hasil layanan.' }[data.status];

            document.getElementById('outputPanel').hidden = false;
            document.getElementById('outputNotYet').hidden = done || !!closed;
            document.getElementById('outputNone').hidden = !closed;
            document.getElementById('outputNoneText').textContent = closed || '';
            document.getElementById('outputDownload').hidden = !(done && output.berkas);
            document.getElementById('outputPickup').hidden = !(done && !output.berkas);
            document.getElementById('outputPickupText').textContent = data.ready_for_pickup
                ? 'Hasil layanan sudah siap dan dapat diambil di Ruang PTSP pada jam layanan.'
                : 'Hasil layanan diserahkan langsung oleh petugas PTSP.';
            document.getElementById('outputSignIn').hidden = output.akun === false;
            document.getElementById('outputVerify').hidden = output.akun === true;
            document.getElementById('outputSignInLink').href = output.masuk_url || '#';
        }

        // Verifikasi sebelum unduh (#verifyModal), for the ticket last tracked.
        let trackedTicket = null;
        const verifyModal = document.getElementById('verifyModal');
        const verifyForm = document.getElementById('verifyForm');
        const verifyInput = document.getElementById('verify_contact');
        const verifyError = document.getElementById('verify_contact_error');
        const verifySubmit = document.getElementById('verifySubmit');

        function verifyProblem(value) {
            const contact = value.trim();
            if (contact === '') return 'Tuliskan email atau nomor WhatsApp yang Anda berikan saat mengajukan.';
            if (contact.includes('@')) return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact) ? null : 'Alamat email belum lengkap.';
            if (!/^[0-9+\-\s().]+$/.test(contact)) return 'Nomor WhatsApp hanya boleh berisi angka (boleh dengan spasi, +, atau -).';
            const digits = contact.replace(/\D/g, '').length;
            return digits < 8 ? 'Nomor WhatsApp terlalu pendek.' : (digits > 15 ? 'Nomor WhatsApp terlalu panjang.' : null);
        }

        function showVerifyProblem(problem) {
            verifyInput.classList.toggle('is-invalid', !!problem);
            verifyInput.setAttribute('aria-invalid', problem ? 'true' : 'false');
            verifyError.textContent = problem || '';
        }

        // Wrong tries and the lock are the server's (it counts them); here they are shown,
        // and a lock is remembered per ticket while the page stays open.
        const VERIFY_ATTEMPTS = {{ \App\Services\TicketDownloadAttempts::ATTEMPTS }};
        const lockedUntil = {};
        let countdown = null;

        function showAttempts(left) {
            const attempts = document.getElementById('verifyAttempts');
            attempts.hidden = left === null || left === undefined;
            attempts.textContent = attempts.hidden ? '' : `Sisa percobaan: ${left} dari ${VERIFY_ATTEMPTS}.`;
        }

        function showLock() {
            clearInterval(countdown);
            const until = lockedUntil[trackedTicket] || 0;
            const tick = () => {
                const seconds = Math.max(0, Math.ceil((until - Date.now()) / 1000));
                const locked = seconds > 0;
                document.getElementById('verifyLock').hidden = !locked;
                verifyInput.disabled = locked;
                verifySubmit.disabled = locked;
                document.getElementById('verifyCountdown').textContent = String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
                if (!locked) {
                    clearInterval(countdown);
                    if (until) { delete lockedUntil[trackedTicket]; showAttempts(null); showVerifyProblem(null); }
                }
            };
            tick();
            countdown = setInterval(tick, 1000);
        }

        document.getElementById('outputVerifyButton').addEventListener('click', function () {
            document.getElementById('verifyTicket').textContent = trackedTicket;
            verifyInput.value = '';
            showVerifyProblem(null);
            showAttempts(null);
            showLock();
            bootstrap.Modal.getOrCreateInstance(verifyModal).show();
        });
        verifyModal.addEventListener('hidden.bs.modal', () => clearInterval(countdown));
        verifyModal.addEventListener('shown.bs.modal', () => verifyInput.focus());
        verifyInput.addEventListener('input', () => showVerifyProblem(null));

        verifyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const problem = verifyProblem(verifyInput.value);
            if (problem) {
                showVerifyProblem(problem);
                verifyInput.focus();
                return;
            }

            verifySubmit.disabled = true;
            fetch('{{ route('onlineportal.track.ticket.download') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                body: JSON.stringify({ ticket_number: trackedTicket, kontak: verifyInput.value.trim() }),
            })
                .then(response => response.json().catch(() => ({})).then(body => ({ status: response.status, body })))
                .then(({ status, body }) => {
                    if (status === 200 && body.unduh) {
                        bootstrap.Modal.getOrCreateInstance(verifyModal).hide();
                        window.location.href = body.unduh; // the file downloads; the page stays
                        return;
                    }
                    if (status === 429) {
                        // Locked: the lock box and its countdown say it.
                        lockedUntil[trackedTicket] = Date.now() + (body.coba_lagi_detik || 60) * 1000;
                        showVerifyProblem(null);
                        showAttempts(null);
                        return;
                    }
                    showVerifyProblem(body.message || 'Verifikasi belum berhasil. Coba lagi.');
                    showAttempts(status === 422 ? body.sisa_percobaan : null);
                })
                .catch(() => showVerifyProblem('Terjadi kesalahan jaringan. Silakan coba lagi.'))
                .finally(() => { verifySubmit.disabled = false; showLock(); });
        });

        function showError(message) {
            errorMessage.style.display = 'block';
            errorText.textContent = message;
            trackingResult.style.display = 'none';
        }
    });
</script>
@endsection
