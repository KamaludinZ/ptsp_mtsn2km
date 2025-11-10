@extends('layouts.admin')

@section('title', 'Tambah Pengaduan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Tambah Pengaduan</h4>
                
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('suadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('suadmin.complaints.index') }}">Pengaduan</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Form Tambah Pengaduan</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('suadmin.complaints.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="complaint_number" class="form-label">Nomor Pengaduan</label>
                                    <input type="text" class="form-control @error('complaint_number') is-invalid @enderror" 
                                           id="complaint_number" name="complaint_number" value="{{ old('complaint_number') }}" required>
                                    @error('complaint_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="type" class="form-label">Jenis</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="pengaduan" {{ old('type') == 'pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                                        <option value="saran" {{ old('type') == 'saran' ? 'selected' : '' }}>Saran</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subjek</label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject') }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategori</label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                        <option value="">Pilih Kategori</option>
                                        <option value="pelayanan" {{ old('category') == 'pelayanan' ? 'selected' : '' }}>Pelayanan</option>
                                        <option value="pegawai" {{ old('category') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                        <option value="fasilitas" {{ old('category') == 'fasilitas' ? 'selected' : '' }}>Fasilitas</option>
                                        <option value="prosedur" {{ old('category') == 'prosedur' ? 'selected' : '' }}>Prosedur</option>
                                        <option value="lainnya" {{ old('category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Prioritas</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="">Pilih Prioritas</option>
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
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reporter_name" class="form-label">Nama Pelapor</label>
                                    <input type="text" class="form-control @error('reporter_name') is-invalid @enderror" 
                                           id="reporter_name" name="reporter_name" value="{{ old('reporter_name') }}">
                                    @error('reporter_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reporter_email" class="form-label">Email Pelapor</label>
                                    <input type="email" class="form-control @error('reporter_email') is-invalid @enderror" 
                                           id="reporter_email" name="reporter_email" value="{{ old('reporter_email') }}">
                                    @error('reporter_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reporter_phone" class="form-label">Telepon Pelapor</label>
                                    <input type="text" class="form-control @error('reporter_phone') is-invalid @enderror" 
                                           id="reporter_phone" name="reporter_phone" value="{{ old('reporter_phone') }}">
                                    @error('reporter_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">Layanan</label>
                                    <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id">
                                        <option value="">Pilih Layanan</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="assigned_to_id" class="form-label">Ditugaskan Ke</label>
                                    <select class="form-select @error('assigned_to_id') is-invalid @enderror" id="assigned_to_id" name="assigned_to_id">
                                        <option value="">Pilih Pegawai</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('assigned_to_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_review" {{ old('status') == 'in_review' ? 'selected' : '' }}>Dalam Review</option>
                                        <option value="resolved" {{ old('status') == 'resolved' ? 'selected' : '' }}>Terselesaikan</option>
                                        <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_whistleblowing" name="is_whistleblowing" value="1" {{ old('is_whistleblowing') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_whistleblowing">Pengaduan Whistleblowing</label>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('suadmin.complaints.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection