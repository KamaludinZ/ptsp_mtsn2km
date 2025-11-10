@extends('layouts.admin')

@section('title', 'Tambah Pengumuman - Super Admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">📝 Tambah Pengumuman Baru</h2>
                    <p class="text-muted mb-0">Buat pengumuman baru untuk disebarkan</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('suadmin.pengumuman.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('suadmin.pengumuman.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-bold">Judul Pengumuman <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control shadow-sm @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title') }}" 
                                           placeholder="Masukkan judul pengumuman">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label fw-bold">Isi Pengumuman <span class="text-danger">*</span></label>
                                    <textarea class="form-control shadow-sm @error('content') is-invalid @enderror" 
                                              id="content" 
                                              name="content" 
                                              rows="10" 
                                              placeholder="Masukkan isi pengumuman">{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label fw-bold">Kategori</label>
                                    <select class="form-select shadow-sm @error('category') is-invalid @enderror" 
                                            id="category" 
                                            name="category">
                                        <option value="">Pilih Kategori</option>
                                        <option value="akademik" {{ old('category') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                                        <option value="administrasi" {{ old('category') == 'administrasi' ? 'selected' : '' }}>Administrasi</option>
                                        <option value="kegiatan" {{ old('category') == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                                        <option value="lainnya" {{ old('category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="author" class="form-label fw-bold">Penulis</label>
                                    <input type="text" 
                                           class="form-control shadow-sm @error('author') is-invalid @enderror" 
                                           id="author" 
                                           name="author" 
                                           value="{{ old('author') ?? auth()->user()->name }}" 
                                           placeholder="Nama penulis pengumuman">
                                    @error('author')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="publish_date" class="form-label fw-bold">Tanggal Publikasi <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control shadow-sm @error('publish_date') is-invalid @enderror" 
                                           id="publish_date" 
                                           name="publish_date" 
                                           value="{{ old('publish_date', now()->format('Y-m-d')) }}">
                                    @error('publish_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label fw-bold">Tanggal Berakhir</label>
                                    <input type="date" 
                                           class="form-control shadow-sm @error('end_date') is-invalid @enderror" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date') }}">
                                    <div class="form-text">Kosongkan jika tidak memiliki tanggal berakhir</div>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="attachment" class="form-label fw-bold">File Edaran</label>
                                    <input type="file" 
                                           class="form-control shadow-sm @error('attachment') is-invalid @enderror" 
                                           id="attachment" 
                                           name="attachment" 
                                           accept=".pdf,.doc,.docx">
                                    <div class="form-text">Format: PDF, DOC, DOCX. Maksimal ukuran: 10MB</div>
                                    @error('attachment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="url" class="form-label fw-bold">URL Dokumen (opsional)</label>
                                    <input type="url" 
                                           class="form-control shadow-sm @error('url') is-invalid @enderror" 
                                           id="url" 
                                           name="url" 
                                           value="{{ old('url') }}"
                                           placeholder="https://contoh.com/dokumen">
                                    <div class="form-text">Gunakan ini jika dokumen berada di situs eksternal (contoh: Google Drive, website lain)</div>
                                    @error('url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_active">Aktif</label>
                                    </div>
                                    <div class="form-text">Jika tidak dicentang, pengumuman tidak akan ditampilkan</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('suadmin.pengumuman.index') }}" class="btn btn-secondary shadow-sm">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection