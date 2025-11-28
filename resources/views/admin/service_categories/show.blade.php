@extends('layouts.admin')

@section('title', 'Detail Kategori Layanan - ' . $serviceCategory->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detail Kategori Layanan: {{ $serviceCategory->name }}</h4>
                    <div>
                        <a href="{{ route('suadmin.service-categories.edit', $serviceCategory) }}" class="btn btn-light me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('suadmin.service-categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Kategori</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->slug }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Parent Category</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->parent->name ?? 'Tidak ada parent' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Urutan</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->sort_order }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Aktif</label>
                                <p class="form-control-plaintext">
                                    @if($serviceCategory->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Icon</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->icon ?? 'Tidak ada icon' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->description ?? '-' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dibuat Tanggal</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->created_at->format('d M Y H:i') }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Diperbarui Tanggal</label>
                                <p class="form-control-plaintext">{{ $serviceCategory->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('suadmin.service-categories.edit', $serviceCategory) }}" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Kategori
                        </a>
                        <a href="{{ route('suadmin.service-categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection