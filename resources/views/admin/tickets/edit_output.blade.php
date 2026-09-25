@extends('layouts.admin')

@section('title', 'Edit Dokumen Hasil - ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Edit Dokumen Hasil</h1>
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.tickets.output.update', [$ticket, $output]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Output</label>
                                    <select name="output_type" class="form-select" required>
                                        <option value="digital" {{ $output->output_type == 'digital' ? 'selected' : '' }}>Digital</option>
                                        <option value="physical" {{ $output->output_type == 'physical' ? 'selected' : '' }}>Fisik</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">File Saat Ini</label>
                                    <br>
                                    @if($output->file_path)
                                        <a href="{{ route('documents.ticket-output', $output) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-file-download me-1"></i>Unduh File
                                        </a>
                                    @else
                                        <em>Tidak ada file</em>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unggah Ulang File (Opsional)</label>
                            <input type="file" name="output_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti file</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="output_description" class="form-control" rows="3">{{ old('output_description', $output->output_description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_delivered" id="is_delivered" 
                                               {{ $output->is_delivered ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_delivered">
                                            Sudah Dikirim/Diserahkan
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pengiriman</label>
                                    <input type="date" name="delivery_date" class="form-control" 
                                           value="{{ old('delivery_date', $output->delivery_date) }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dikirim Kepada</label>
                            <select name="delivered_to" class="form-select">
                                <option value="">Pilih Penerima</option>
                                @foreach($ticket->service->users ?? [] as $user)
                                    <option value="{{ $user->id }}" 
                                        {{ old('delivered_to', $output->delivered_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection