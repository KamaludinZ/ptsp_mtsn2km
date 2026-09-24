@extends('layouts.admin')

@section('title', 'Tambah Tiket Baru')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">📝 Tambah Tiket Layanan Baru</h2>
                    <p class="text-muted mb-0">Buat tiket layanan baru untuk pengguna</p>
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
                        <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Tambah Informasi Tiket</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pemohon <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="user_id" class="form-select" id="userSelect">
                                            <option value="">Pilih Pengguna</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" id="manualEntryBtn">Manual Entry</button>
                                    </div>
                                    <small class="form-text text-muted">Pilih dari daftar atau isi manual</small>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Layanan <span class="text-danger">*</span></label>
                                    <select name="service_id" class="form-select shadow-sm @error('service_id') is-invalid @enderror" id="serviceSelect">
                                        <option value="">Pilih Layanan</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" data-mode="{{ $service->mode }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manual Entry Fields (Initially Hidden) -->
                        <div id="manualEntryFields" class="d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Pemohon <span class="text-danger">*</span></label>
                                        <input type="text" name="applicant_name" class="form-control shadow-sm @error('applicant_name') is-invalid @enderror" 
                                               placeholder="Masukkan nama pemohon" value="{{ old('applicant_name') }}">
                                        @error('applicant_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" name="email" class="form-control shadow-sm @error('email') is-invalid @enderror" 
                                               placeholder="Masukkan email pemohon" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nomor WhatsApp</label>
                                        <input type="text" name="whatsapp_number" class="form-control shadow-sm @error('whatsapp_number') is-invalid @enderror" 
                                               placeholder="Masukkan nomor WhatsApp" value="{{ old('whatsapp_number') }}">
                                        @error('whatsapp_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select shadow-sm @error('status') is-invalid @enderror">
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_process" {{ old('status') == 'in_process' ? 'selected' : '' }}>Diproses</option>
                                        <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Prioritas <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select shadow-sm @error('priority') is-invalid @enderror">
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Darurat</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mode <span class="text-danger">*</span></label>
                                    <select name="mode" class="form-select shadow-sm @error('mode') is-invalid @enderror" id="modeSelect">
                                        <option value="online" {{ old('mode') == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="offline" {{ old('mode') == 'offline' ? 'selected' : '' }}>Offline</option>
                                        <option value="hybrid" {{ old('mode') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="notes" class="form-control shadow-sm @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload Berkas Persyaratan</label>
                            <input type="file" name="requirement_files[]" class="form-control shadow-sm @error('requirement_files') is-invalid @enderror" multiple>
                            <small class="form-text text-muted">Upload berkas persyaratan jika diperlukan (Format: PDF, JPG, PNG, DOC, DOCX. Max: 10MB per file)</small>
                            @error('requirement_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary shadow-sm">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('userSelect');
    const manualEntryBtn = document.getElementById('manualEntryBtn');
    const manualEntryFields = document.getElementById('manualEntryFields');
    const serviceSelect = document.getElementById('serviceSelect');
    const modeSelect = document.getElementById('modeSelect');
    
    // Toggle between user selection and manual entry
    manualEntryBtn.addEventListener('click', function() {
        if (manualEntryFields.classList.contains('d-none')) {
            // Switch to manual entry
            userSelect.value = '';
            manualEntryFields.classList.remove('d-none');
            userSelect.closest('.input-group').querySelector('.btn').textContent = 'Pilih User';
        } else {
            // Switch back to user selection
            document.querySelector('[name="applicant_name"]').value = '';
            document.querySelector('[name="email"]').value = '';
            document.querySelector('[name="whatsapp_number"]').value = '';
            manualEntryFields.classList.add('d-none');
            userSelect.closest('.input-group').querySelector('.btn').textContent = 'Manual Entry';
        }
    });
    
    // Auto-detect mode based on selected service
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const serviceMode = selectedOption.getAttribute('data-mode');
        
        if (serviceMode) {
            modeSelect.value = serviceMode;
        }
    });
});
</script>
@endsection