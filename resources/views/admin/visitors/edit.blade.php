@extends('layouts.admin')

@section('title', 'Edit Pengunjung - ' . $visitor->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">✏️ Edit Pengunjung</h2>
                    <p class="text-muted mb-0">Perbarui informasi pengunjung</p>
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
                        <h5 class="card-title mb-0"><i class="fas fa-user-edit me-2"></i>Edit Informasi Pengunjung</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.visitors.update', $visitor) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control shadow-sm @error('name') is-invalid @enderror" value="{{ old('name', $visitor->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control shadow-sm @error('email') is-invalid @enderror" value="{{ old('email', $visitor->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Telepon</label>
                                    <input type="text" name="phone" class="form-control shadow-sm @error('phone') is-invalid @enderror" value="{{ old('phone', $visitor->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Institusi/Keperluan</label>
                                    <input type="text" name="institution" class="form-control shadow-sm @error('institution') is-invalid @enderror" value="{{ old('institution', $visitor->institution) }}">
                                    @error('institution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pihak yang Dituju</label>
                                    <input type="text" name="person_to_visit" class="form-control shadow-sm @error('person_to_visit') is-invalid @enderror" value="{{ old('person_to_visit', $visitor->person_to_visit) }}">
                                    @error('person_to_visit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tujuan</label>
                                    <input type="text" name="purpose" class="form-control shadow-sm @error('purpose') is-invalid @enderror" value="{{ old('purpose', $visitor->purpose) }}">
                                    @error('purpose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="notes" class="form-control shadow-sm @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $visitor->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Foto Saat Ini</label>
                                    @if($visitor->photo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $visitor->photo) }}" 
                                                 alt="Foto Pengunjung" 
                                                 class="img-fluid rounded" 
                                                 style="max-width: 200px; height: auto;">
                                        </div>
                                    @else
                                        <p class="text-muted">Tidak ada foto</p>
                                    @endif
                                    <label class="form-label fw-bold mt-2">Ganti Foto</label>
                                    <input type="file" name="photo" class="form-control shadow-sm @error('photo') is-invalid @enderror" accept="image/*">
                                    <div class="form-text">Format: JPEG, PNG. Maksimal: 2MB</div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Privasi Foto</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_obscured" id="is_obscured" {{ old('is_obscured', $visitor->is_obscured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_obscured">Privasi (Foto tidak ditampilkan)</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status Check-out</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="check_out_time" id="check_out_time" 
                                               {{ old('check_out_time') || $visitor->check_out_time ? 'checked' : '' }}
                                               value="1">
                                        <label class="form-check-label" for="check_out_time">
                                            Pengunjung telah checkout
                                        </label>
                                    </div>
                                    <div class="form-text">Centang jika pengunjung telah selesai kunjungan</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.visitors.index') }}" class="btn btn-secondary shadow-sm">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Pengunjung
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection