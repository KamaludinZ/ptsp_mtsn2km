@extends('layouts.admin')

@section('title', 'Detail Tiket - ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">🎫 Detail Tiket Layanan</h2>
                    <p class="text-muted mb-0">Informasi lengkap tentang tiket layanan</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-amber-600 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-ticket-alt me-2"></i>Informasi Tiket</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor Tiket</label>
                                <p class="mb-0">{{ $ticket->ticket_number }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($ticket->status == 'pending') bg-warning-subtle text-warning
                                        @elseif($ticket->status == 'in_progress') bg-info-subtle text-info
                                        @elseif($ticket->status == 'pending_approval') bg-primary-subtle text-primary
                                        @elseif($ticket->status == 'approved') bg-success-subtle text-success
                                        @elseif($ticket->status == 'completed') bg-success-subtle text-success
                                        @elseif($ticket->status == 'rejected') bg-danger-subtle text-danger
                                        @else bg-secondary-subtle text-secondary @endif">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pemohon</label>
                                <p class="mb-0">{{ $ticket->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Layanan</label>
                                <p class="mb-0">{{ $ticket->service->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Prioritas</label>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($ticket->priority == 'low') bg-success-subtle text-success
                                        @elseif($ticket->priority == 'normal') bg-info-subtle text-info
                                        @elseif($ticket->priority == 'high') bg-warning-subtle text-warning
                                        @elseif($ticket->priority == 'urgent') bg-danger-subtle text-danger
                                        @else bg-secondary-subtle text-secondary @endif">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode</label>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($ticket->mode == 'online') bg-success-subtle text-success
                                        @elseif($ticket->mode == 'offline') bg-warning-subtle text-warning
                                        @elseif($ticket->mode == 'hybrid') bg-info-subtle text-info
                                        @endif">
                                        {{ ucfirst($ticket->mode) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="mb-0">{{ $ticket->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">WhatsApp</label>
                                <p class="mb-0">{{ $ticket->whatsapp_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Dibuat</label>
                                <p class="mb-0">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Diperbarui</label>
                                <p class="mb-0">{{ $ticket->updated_at ? $ticket->updated_at->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($ticket->notes)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan</label>
                                    <p class="mb-0">{{ $ticket->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($ticket->assignedTo)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ditugaskan Kepada</label>
                                    <p class="mb-0">{{ $ticket->assignedTo->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Requirements Files Section -->
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Berkas Persyaratan</h5>
                        
                        @if($ticket->files->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama File</th>
                                            <th>Diupload Oleh</th>
                                            <th>Tanggal Upload</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ticket->files as $file)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file me-2 text-muted"></i>
                                                    <div>
                                                        <div class="fw-medium">{{ $file->file_name }}</div>
                                                        <small class="text-muted">{{ number_format($file->size ?? filesize(storage_path('app/'.$file->file_path)), 2, ',', '.') }} KB</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $file->uploader->name ?? 'N/A' }}</td>
                                            <td>{{ $file->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <a href="{{ Storage::url($file->file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-download me-1"></i>Lihat
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada berkas persyaratan yang diupload
                            </div>
                        @endif
                        
                        <!-- File Upload Form -->
                        <div class="mt-3 p-3 border rounded bg-light">
                            <h6 class="mb-3"><i class="fas fa-upload me-2"></i>Upload Berkas Persyaratan</h6>
                            <form action="{{ route('admin.tickets.upload.requirement', $ticket) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="file" name="requirement_file" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Output Documents Section -->
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-file-export me-2"></i>Dokumen Hasil</h5>
                        
                        @if($ticket->outputs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Nama Dokumen</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Dikirim Kepada</th>
                                            <th>Tanggal Pengiriman</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ticket->outputs as $output)
                                        <tr>
                                            <td>
                                                <span class="badge 
                                                    @if($output->output_type == 'digital') bg-success
                                                    @else bg-info @endif">
                                                    {{ ucfirst($output->output_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($output->file_path)
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file me-2 text-muted"></i>
                                                        {{ basename($output->file_path) }}
                                                    </div>
                                                @else
                                                    <em>Tidak ada file</em>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($output->output_description, 50) }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($output->is_delivered) bg-success
                                                    @else bg-warning @endif">
                                                    @if($output->is_delivered) Sudah Dikirim @else Menunggu @endif
                                                </span>
                                            </td>
                                            <td>{{ $output->deliveredTo->name ?? 'N/A' }}</td>
                                            <td>{{ $output->delivery_date ? $output->delivery_date->format('d M Y') : '-' }}</td>
                                            <td>
                                                @if($output->file_path)
                                                <a href="{{ Storage::url($output->file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-outline-success btn-sm me-1">
                                                    <i class="fas fa-download me-1"></i>Lihat
                                                </a>
                                                @endif
                                                <a href="{{ route('admin.tickets.output.edit', [$ticket, $output]) }}" 
                                                   class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada dokumen hasil yang diupload
                            </div>
                        @endif
                        
                        <!-- Output Upload Form -->
                        <div class="mt-3 p-3 border rounded bg-light">
                            <h6 class="mb-3"><i class="fas fa-file-upload me-2"></i>Upload Dokumen Hasil</h6>
                            <form action="{{ route('admin.tickets.upload.output', $ticket) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <select name="output_type" class="form-select" required>
                                            <option value="">Pilih Jenis</option>
                                            <option value="digital">Digital</option>
                                            <option value="physical">Fisik</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="file" name="output_file" class="form-control">
                                        <small class="text-muted">Opsional: jika dokumen disimpan secara digital</small>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <textarea name="output_description" class="form-control" placeholder="Deskripsi dokumen hasil..." rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Physical Collection Message for Offline Services -->
                        @if($ticket->service && $ticket->service->mode !== 'online')
                        <div class="mt-3 p-3 border rounded bg-info bg-opacity-10 border-info">
                            <h6 class="mb-2 text-info"><i class="fas fa-exclamation-circle me-2"></i>Pengambilan Dokumen</h6>
                            <p class="mb-0 text-info">
                                Layanan ini merupakan layanan offline. Harap informasikan kepada pemohon bahwa dokumen hasil 
                                dapat diambil di ruang PTSP pada jam kerja (08:00 - 15:00).
                            </p>
                        </div>
                        @endif
                        
                        <!-- Approval Section -->
                        @if($ticket->approval_required)
                        <div class="mt-4">
                            <h5 class="mb-3"><i class="fas fa-check-circle me-2"></i>Verifikasi Persyaratan</h5>
                            
                            <div class="card border-{{ $ticket->is_approved ? 'success' : ($ticket->approval_required && !$ticket->is_approved ? 'warning' : 'secondary') }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                Status Persetujuan: 
                                                @if($ticket->is_approved)
                                                    <span class="badge bg-success">Disetujui</span>
                                                    @if($ticket->approver)
                                                        oleh {{ $ticket->approver->name }}
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">Belum Disetujui</span>
                                                @endif
                                            </h6>
                                            @if($ticket->approved_at)
                                                <small class="text-muted">Disetujui pada {{ $ticket->approved_at->format('d M Y H:i') }}</small>
                                            @endif
                                            @if($ticket->approval_notes)
                                                <p class="mb-0 mt-2"><strong>Catatan:</strong> {{ $ticket->approval_notes }}</p>
                                            @endif
                                        </div>
                                        
                                        @if(!$ticket->is_approved)
                                        <div class="btn-group">
                                            @can('approve', $ticket)
                                            <button class="btn btn-success btn-sm approve-ticket-btn" data-ticket-id="{{ $ticket->id }}">
                                                <i class="fas fa-check me-1"></i>Setujui
                                            </button>
                                            <button class="btn btn-danger btn-sm reject-ticket-btn" data-ticket-id="{{ $ticket->id }}">
                                                <i class="fas fa-times me-1"></i>Tolak
                                            </button>
                                            @endcan
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>

                        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>

                        <!-- Send Ticket Info Button -->
                        <button type="button" class="btn btn-info shadow-sm send-ticket-info-btn" data-ticket-id="{{ $ticket->id }}">
                            <i class="bi bi-envelope me-2"></i>Kirim Info Tiket
                        </button>

                        <!-- Send Survey Button - Auto disable if already filled -->
                        @if($ticket->status === 'completed')
                            @php
                                $hasSurveyResponse = \App\Models\SurveyResponse::where('ticket_code', $ticket->ticket_number)->exists();
                            @endphp
                            <button type="button"
                                    class="btn btn-warning shadow-sm send-survey-btn"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-ticket-number="{{ $ticket->ticket_number }}"
                                    {{ ($ticket->survey_sent || $hasSurveyResponse) ? 'disabled' : '' }}>
                                <i class="bi bi-clipboard-check me-2"></i>
                                @if($hasSurveyResponse)
                                    Survei Sudah Diisi
                                @elseif($ticket->survey_sent)
                                    Survei Sudah Dikirim
                                @else
                                    Kirim Survei
                                @endif
                            </button>
                        @endif

                        <!-- Approval Action Buttons -->
                        @if($ticket->approval_status === 'approved' && $ticket->service->is_digital_product)
                            <button type="button" class="btn btn-success shadow-sm upload-result-btn" data-ticket-id="{{ $ticket->id }}">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Hasil
                            </button>
                        @elseif($ticket->approval_status === 'approved' && !$ticket->service->is_digital_product)
                            <button type="button" class="btn btn-success shadow-sm mark-pickup-btn" data-ticket-id="{{ $ticket->id }}" {{ $ticket->ready_for_pickup ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle me-2"></i>
                                {{ $ticket->ready_for_pickup ? 'Sudah Siap Diambil' : 'Tandai Siap Diambil' }}
                            </button>
                        @endif

                        <form action="{{ route('admin.tickets.destroy', $ticket) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger shadow-sm"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus tiket ini?')">
                                <i class="fas fa-trash me-2"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve ticket button
    const approveButtons = document.querySelectorAll('.approve-ticket-btn');
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            
            // Simple confirmation, in a real implementation you might want a modal with notes
            if (confirm('Apakah Anda yakin ingin menyetujui tiket ini?')) {
                fetch(`/admin/tickets/${ticketId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menyetujui tiket: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error terjadi saat menyetujui tiket');
                });
            }
        });
    });
    
    // Reject ticket button
    const rejectButtons = document.querySelectorAll('.reject-ticket-btn');
    rejectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            
            // Simple confirmation, in a real implementation you might want a modal for notes
            if (confirm('Apakah Anda yakin ingin menolak tiket ini?')) {
                fetch(`/admin/tickets/${ticketId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menolak tiket: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error terjadi saat menolak tiket');
                });
            }
        });
    });

    // Send Ticket Info button
    const sendTicketInfoButtons = document.querySelectorAll('.send-ticket-info-btn');
    sendTicketInfoButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');

            if (confirm('Kirim informasi tiket ke pemohon?')) {
                fetch(`/admin/tickets/${ticketId}/send-ticket-info`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('Gagal mengirim info tiket: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error terjadi saat mengirim info tiket');
                });
            }
        });
    });

    // Send Survey button
    const sendSurveyButtons = document.querySelectorAll('.send-survey-btn');
    sendSurveyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            const ticketNumber = this.getAttribute('data-ticket-number');

            if (confirm('Kirim link survei kepuasan masyarakat ke pemohon?')) {
                fetch(`/admin/tickets/${ticketId}/send-survey`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Disable button and change text
                        this.disabled = true;
                        this.innerHTML = '<i class="bi bi-clipboard-check me-2"></i>Survei Sudah Dikirim';
                    } else {
                        alert('Gagal mengirim survei: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error terjadi saat mengirim survei');
                });
            }
        });
    });

    // Upload Result button
    const uploadResultButtons = document.querySelectorAll('.upload-result-btn');
    uploadResultButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            // TODO: Show modal with file upload form
            alert('Fitur upload hasil akan segera tersedia. Gunakan menu Edit Tiket untuk sementara.');
        });
    });

    // Mark Ready for Pickup button
    const markPickupButtons = document.querySelectorAll('.mark-pickup-btn');
    markPickupButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');

            if (confirm('Tandai dokumen siap diambil di PTSP? Notifikasi akan dikirim kepada pemohon.')) {
                fetch(`/admin/tickets/${ticketId}/mark-ready-pickup`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Reload page to update button state
                        location.reload();
                    } else {
                        alert('Gagal menandai dokumen: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error terjadi saat menandai dokumen');
                });
            }
        });
    });
});
</script>
@endpush

@endsection