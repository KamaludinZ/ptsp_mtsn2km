@extends('layouts.admin')

@section('title', 'Edit Layanan - ' . $service->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">Edit Layanan: {{ $service->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('suadmin.services.update', $service) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <ul class="nav nav-tabs" id="serviceTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Informasi Dasar</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="components-tab" data-bs-toggle="tab" data-bs-target="#components" type="button" role="tab">14 Komponen</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $service->slug) }}" required>
                                            @error('slug')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Slug akan digunakan untuk URL layanan</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <select name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror">
                                                <option value="">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('service_category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mode <span class="text-danger">*</span></label>
                                            <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                                                <option value="online" {{ old('mode', $service->mode) == 'online' ? 'selected' : '' }}>Online</option>
                                                <option value="offline" {{ old('mode', $service->mode) == 'offline' ? 'selected' : '' }}>Offline</option>
                                                <option value="both" {{ old('mode', $service->mode) == 'both' ? 'selected' : '' }}>Both</option>
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
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">Aktif</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_digital_product" id="is_digital_product" {{ old('is_digital_product', $service->is_digital_product) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_digital_product">Produk Digital</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="approval_required" id="approval_required" {{ old('approval_required', $service->approval_required) ? 'checked' : 'checked' }}>
                                        <label class="form-check-label" for="approval_required">Memerlukan Persetujuan</label>
                                    </div>
                                </div>
                                
                                <div id="approval_settings" class="border rounded p-3 mb-3 bg-light" style="{{ old('approval_required', $service->approval_required) ? '' : 'display: none;' }}">
                                    <h6 class="text-primary"><i class="bi bi-shield-check me-2"></i>Pengaturan Alur Persetujuan</h6>
                                    <p class="text-muted small">Konfigurasi siapa yang dapat melakukan approval dokumen persyaratan yang masuk (online, offline, hybrid)</p>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Peran yang dapat melakukan Approval</label>
                                        <select name="approval_roles[]" class="form-select @error('approval_roles') is-invalid @enderror" multiple size="4">
                                            <option value="admin" {{ in_array('admin', old('approval_roles', $service->approval_roles ?? [])) ? 'selected' : '' }}>Admin</option>
                                            <option value="staff" {{ in_array('staff', old('approval_roles', $service->approval_roles ?? [])) ? 'selected' : '' }}>Staff</option>
                                            <option value="operator" {{ in_array('operator', old('approval_roles', $service->approval_roles ?? [])) ? 'selected' : '' }}>Operator</option>
                                        </select>
                                        <div class="form-text">Tekan Ctrl/Cmd untuk memilih lebih dari satu peran</div>
                                        @error('approval_roles')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Instruksi Proses Approval</label>
                                        <textarea name="approval_instructions" class="form-control @error('approval_instructions') is-invalid @enderror" rows="3" placeholder="Contoh: Petugas harus memeriksa kelengkapan dokumen persyaratan dan kesesuaian data...">{{ old('approval_instructions', $service->approval_instructions) }}</textarea>
                                        <div class="form-text">Panduan untuk petugas yang melakukan approval</div>
                                        @error('approval_instructions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-info py-2 mb-0">
                                        <small><i class="bi bi-info-circle me-1"></i><strong>Catatan:</strong> Setelah approval, petugas dapat upload hasil (untuk produk digital) atau menandai dokumen siap diambil di PTSP</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $service->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Estimasi Hari</label>
                                    <input type="number" name="estimated_days" class="form-control @error('estimated_days') is-invalid @enderror" value="{{ old('estimated_days', $service->estimated_days) }}" min="1">
                                    @error('estimated_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- 14 Components Tab -->
                            <div class="tab-pane fade" id="components" role="tabpanel">
                                <div class="alert alert-info">
                                    <h5>14 Komponen Standar Pelayanan (Permen PANRB 15/2014)</h5>
                                    <p>Lengkapi semua komponen standar pelayanan sesuai Peraturan Menteri PANRB Nomor 15 Tahun 2014</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">1. Dasar Hukum</label>
                                            <textarea name="dasar_hukum" class="form-control @error('dasar_hukum') is-invalid @enderror" rows="3">{{ old('dasar_hukum', $service->dasar_hukum) }}</textarea>
                                            @error('dasar_hukum')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">2. Persyaratan</label>
                                            <textarea name="persyaratan" class="form-control @error('persyaratan') is-invalid @enderror" rows="3">{{ old('persyaratan', $service->persyaratan) }}</textarea>
                                            @error('persyaratan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">3. Sistem, Mekanisme, Prosedur</label>
                                            <textarea name="mekanisme" class="form-control @error('mekanisme') is-invalid @enderror" rows="3">{{ old('mekanisme', $service->mekanisme) }}</textarea>
                                            @error('mekanisme')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">4. Jangka Waktu Penyelesaian</label>
                                            <textarea name="jangka_waktu" class="form-control @error('jangka_waktu') is-invalid @enderror" rows="3">{{ old('jangka_waktu', $service->jangka_waktu) }}</textarea>
                                            @error('jangka_waktu')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">5. Biaya/Tarif</label>
                                            <textarea name="biaya_tarif" class="form-control @error('biaya_tarif') is-invalid @enderror" rows="3">{{ old('biaya_tarif', $service->biaya_tarif) }}</textarea>
                                            @error('biaya_tarif')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">6. Produk Pelayanan</label>
                                            <textarea name="produk_layanan" class="form-control @error('produk_layanan') is-invalid @enderror" rows="3">{{ old('produk_layanan', $service->produk_layanan) }}</textarea>
                                            @error('produk_layanan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">7. Sarana & Prasarana</label>
                                            <textarea name="sarpras" class="form-control @error('sarpras') is-invalid @enderror" rows="3">{{ old('sarpras', $service->sarpras) }}</textarea>
                                            @error('sarpras')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">8. Kompetensi Pelaksana</label>
                                            <textarea name="kompetensi_pelaksana" class="form-control @error('kompetensi_pelaksana') is-invalid @enderror" rows="3">{{ old('kompetensi_pelaksana', $service->kompetensi_pelaksana) }}</textarea>
                                            @error('kompetensi_pelaksana')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">9. Pengawasan Internal</label>
                                            <textarea name="pengawasan_internal" class="form-control @error('pengawasan_internal') is-invalid @enderror" rows="3">{{ old('pengawasan_internal', $service->pengawasan_internal) }}</textarea>
                                            @error('pengawasan_internal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">10. Penanganan Pengaduan</label>
                                            <textarea name="penanganan_pengaduan" class="form-control @error('penanganan_pengaduan') is-invalid @enderror" rows="3">{{ old('penanganan_pengaduan', $service->penanganan_pengaduan) }}</textarea>
                                            @error('penanganan_pengaduan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">11. Jumlah Pelaksana</label>
                                            <textarea name="jumlah_pelaksana" class="form-control @error('jumlah_pelaksana') is-invalid @enderror" rows="3">{{ old('jumlah_pelaksana', $service->jumlah_pelaksana) }}</textarea>
                                            @error('jumlah_pelaksana')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">12. Jaminan Pelayanan</label>
                                            <textarea name="jaminan_pelayanan" class="form-control @error('jaminan_pelayanan') is-invalid @enderror" rows="3">{{ old('jaminan_pelayanan', $service->jaminan_pelayanan) }}</textarea>
                                            @error('jaminan_pelayanan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">13. Jaminan Keamanan</label>
                                            <textarea name="jaminan_keamanan" class="form-control @error('jaminan_keamanan') is-invalid @enderror" rows="3">{{ old('jaminan_keamanan', $service->jaminan_keamanan) }}</textarea>
                                            @error('jaminan_keamanan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">14. Evaluasi Kinerja</label>
                                            <textarea name="evaluasi_kinerja" class="form-control @error('evaluasi_kinerja') is-invalid @enderror" rows="3">{{ old('evaluasi_kinerja', $service->evaluasi_kinerja) }}</textarea>
                                            @error('evaluasi_kinerja')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('suadmin.services.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Update Layanan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approvalRequiredCheckbox = document.getElementById('approval_required');
    const approvalSettingsDiv = document.getElementById('approval_settings');
    
    approvalRequiredCheckbox.addEventListener('change', function() {
        if (this.checked) {
            approvalSettingsDiv.style.display = 'block';
        } else {
            approvalSettingsDiv.style.display = 'none';
        }
    });
    
    // Initialize based on initial state
    if (!approvalRequiredCheckbox.checked) {
        approvalSettingsDiv.style.display = 'none';
    }
});
</script>
@endpush

@endsection