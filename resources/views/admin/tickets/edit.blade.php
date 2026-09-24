@extends('layouts.admin')

@section('title', 'Edit Tiket - ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">✏️ Edit Tiket Layanan</h2>
                    <p class="text-muted mb-0">Perbarui informasi tiket layanan</p>
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
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>Edit Informasi Tiket</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pemohon</label>
                                    <select name="user_id" class="form-select shadow-sm @error('user_id') is-invalid @enderror">
                                        <option value="">Pilih Pengguna</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $ticket->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Layanan</label>
                                    <select name="service_id" class="form-select shadow-sm @error('service_id') is-invalid @enderror">
                                        <option value="">Pilih Layanan</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id', $ticket->service_id) == $service->id ? 'selected' : '' }}>
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
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select shadow-sm @error('status') is-invalid @enderror">
                                        <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_process" {{ old('status', $ticket->status) == 'in_process' ? 'selected' : '' }}>Diproses</option>
                                        <option value="pending_approval" {{ old('status', $ticket->status) == 'pending_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                                        <option value="approved" {{ old('status', $ticket->status) == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="completed" {{ old('status', $ticket->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                                        <option value="rejected" {{ old('status', $ticket->status) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Prioritas</label>
                                    <select name="priority" class="form-select shadow-sm @error('priority') is-invalid @enderror">
                                        <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Rendah</option>
                                        <option value="normal" {{ old('priority', $ticket->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>Tinggi</option>
                                        <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>Darurat</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mode</label>
                                    <select name="mode" class="form-select shadow-sm @error('mode') is-invalid @enderror">
                                        <option value="online" {{ old('mode', $ticket->mode) == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="offline" {{ old('mode', $ticket->mode) == 'offline' ? 'selected' : '' }}>Offline</option>
                                        <option value="hybrid" {{ old('mode', $ticket->mode) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                    @error('mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control shadow-sm @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $ticket->email) }}" placeholder="Email pemohon">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nomor WhatsApp</label>
                                    <input type="text" name="whatsapp_number" class="form-control shadow-sm @error('whatsapp_number') is-invalid @enderror" 
                                           value="{{ old('whatsapp_number', $ticket->whatsapp_number) }}" placeholder="Nomor WhatsApp pemohon">
                                    @error('whatsapp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="notes" class="form-control shadow-sm @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $ticket->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary shadow-sm">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection