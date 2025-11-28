@extends('layouts.admin')

@section('title', 'Detail Pengunjung - ' . $visitor->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">👤 Detail Pengunjung</h2>
                    <p class="text-muted mb-0">Informasi lengkap tentang pengunjung</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.visitors.index') }}" class="btn btn-secondary shadow-sm">
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
                        <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Informasi Pengunjung</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama</label>
                                <p class="mb-0">{{ $visitor->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="mb-0">{{ $visitor->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Telepon</label>
                                <p class="mb-0">{{ $visitor->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Institusi/Keperluan</label>
                                <p class="mb-0">{{ $visitor->institution ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pihak yang Dituju</label>
                                <p class="mb-0">{{ $visitor->person_to_visit ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tujuan Kunjungan</label>
                                <p class="mb-0">{{ $visitor->purpose ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Check-in</label>
                                <p class="mb-0">{{ $visitor->check_in_time ? $visitor->check_in_time->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Check-out</label>
                                <p class="mb-0">{{ $visitor->check_out_time ? $visitor->check_out_time->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="mb-0">
                                    @if($visitor->check_out_time)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-check-circle me-1"></i> Sudah Checkout
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="fas fa-clock me-1"></i> Aktif
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Privasi Foto</label>
                                <p class="mb-0">
                                    @if($visitor->is_obscured)
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="fas fa-eye-slash me-1"></i> Foto Disembunyikan
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-eye me-1"></i> Foto Ditampilkan
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($visitor->notes)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan</label>
                                    <p class="mb-0">{{ $visitor->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($visitor->photo)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Foto</label>
                                    <div>
                                        <img src="{{ asset('storage/' . $visitor->photo) }}" 
                                             alt="Foto Pengunjung" 
                                             class="img-fluid rounded" 
                                             style="max-width: 300px; height: auto; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.visitors.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        
                        @if(!$visitor->check_out_time)
                            <form action="{{ route('admin.visitors.checkout', $visitor) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning shadow-sm" 
                                        onclick="return confirm('Apakah Anda yakin pengunjung ini telah selesai dan ingin di-checkout?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Checkout
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('admin.visitors.edit', $visitor) }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        
                        <form action="{{ route('admin.visitors.destroy', $visitor) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger shadow-sm" 
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data pengunjung ini?')">
                                <i class="fas fa-trash me-2"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection