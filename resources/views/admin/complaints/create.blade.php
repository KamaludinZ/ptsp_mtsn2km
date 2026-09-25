@extends('layouts.admin')

@section('title', 'Catat Pengaduan')

@section('content')
<div class="container-fluid py-4" style="max-width: 820px;">
    <a href="{{ route('admin.complaints.index') }}" class="small d-inline-block mb-3"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke daftar pengaduan</a>
    <h1 class="h3 fw-bold mb-1">Catat Pengaduan Offline</h1>
    <p class="text-muted mb-4">Untuk pengaduan yang diterima lewat surat, telepon, atau langsung di madrasah.</p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.complaints.store') }}" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label for="complaint_type" class="form-label">Jenis</label>
                        <select id="complaint_type" name="complaint_type" class="form-select @error('complaint_type') is-invalid @enderror" required>
                            <option value="complaint" @selected(old('complaint_type') === 'complaint')>Pengaduan</option>
                            <option value="suggestion" @selected(old('complaint_type') === 'suggestion')>Saran</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="priority" class="form-label">Prioritas</label>
                        <select id="priority" name="priority" class="form-select" required>
                            @foreach (\App\Models\Complaint::PRIORITIES as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="service_id" class="form-label">Layanan terkait</label>
                        <select id="service_id" name="service_id" class="form-select">
                            <option value="">Tidak ada</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="title" class="form-label">Judul</label>
                        <input id="title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Isi pengaduan</label>
                        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="reporter_name" class="form-label">Nama pelapor</label>
                        <input id="reporter_name" name="reporter_name" value="{{ old('reporter_name') }}" class="form-control">
                    </div>
                    <div class="col-sm-4">
                        <label for="reporter_email" class="form-label">Email</label>
                        <input id="reporter_email" type="email" name="reporter_email" value="{{ old('reporter_email') }}" class="form-control @error('reporter_email') is-invalid @enderror">
                        @error('reporter_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="reporter_phone" class="form-label">Telepon</label>
                        <input id="reporter_phone" name="reporter_phone" value="{{ old('reporter_phone') }}" class="form-control">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
