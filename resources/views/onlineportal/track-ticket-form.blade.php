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
                                <p class="mb-0"><span class="status-badge" id="statusBadge">-</span></p>
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

                        <!-- Action Buttons -->
                        <div class="mt-4" id="actionButtons" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <a href="#" class="action-button action-button-primary w-100" id="downloadButton">
                                        <i class="fas fa-download me-2"></i>Unduh Produk Digital
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="#" class="action-button action-button-outline w-100" id="collectButton">
                                        <i class="fas fa-file-alt me-2"></i>Ambil di Ruang PTSP
                                    </a>
                                </div>
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
        const actionButtons = document.getElementById('actionButtons');
        const downloadButton = document.getElementById('downloadButton');
        const collectButton = document.getElementById('collectButton');

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
                    ticket_number: ticketNumber
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
                    document.getElementById('description').textContent = data.description || '-';
                    
                    // Update status badge
                    const statusBadge = document.getElementById('statusBadge');
                    const statusLabels = {
                        submitted: 'Diajukan', verified: 'Diverifikasi', in_process: 'Sedang Diproses',
                        approved: 'Disetujui', rejected: 'Ditolak', completed: 'Selesai', cancelled: 'Dibatalkan',
                    };
                    statusBadge.textContent = statusLabels[data.status] || data.status;
                    statusBadge.className = 'status-badge';
                    
                    // Add status class based on status value
                    if (data.status === 'completed') {
                        statusBadge.classList.add('status-completed');
                    } else if (['verified', 'in_process', 'approved'].includes(data.status)) {
                        statusBadge.classList.add('status-processing');
                    } else {
                        statusBadge.classList.add('status-pending');
                    }
                    
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
                                    <td>${escapeHtml(log.action || 'Aktivitas')}</td>
                                    <td><span class="status-badge status-completed">Terupdate</span></td>
                                    <td>${logDate}</td>
                                    <td>${escapeHtml(log.notes || 'Tidak ada catatan')}</td>
                                `;
                                processTableBody.appendChild(row);
                            }
                        });
                    }
                    
                    // Show action buttons based on service mode and status
                    if (data.status === 'completed') {
                        actionButtons.style.display = 'block';
                        
                        // Determine which button to show based on service mode
                        if (data.service?.mode === 'online' && data.has_output_file) {
                            downloadButton.style.display = 'inline-block';
                            collectButton.style.display = 'none';
                            downloadButton.href = `{{ url('/portal/tickets') }}/${encodeURIComponent(data.ticket_number)}/download`;
                        } else {
                            downloadButton.style.display = 'none';
                            collectButton.style.display = 'inline-block';
                            collectButton.href = '#'; // Or link to directions
                        }
                    } else {
                        actionButtons.style.display = 'none';
                    }
                    
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
        
        function showError(message) {
            errorMessage.style.display = 'block';
            errorText.textContent = message;
            trackingResult.style.display = 'none';
        }
    });
</script>
@endsection
