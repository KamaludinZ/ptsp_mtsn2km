@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat - Identitas Responden')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Survey Kepuasan Masyarakat
                </h2>
                <p class="text-muted">MTsN 2 Kota Malang</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 1 dari 3</span>
                    <span class="text-muted small">33%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 33%;"
                         aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Tahap 1: Identitas Responden
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Silakan lengkapi data diri Anda terlebih dahulu. Semua informasi akan dijaga kerahasiaannya.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Terjadi kesalahan!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('survey.step1.store') }}" method="POST" id="step1Form">
                        @csrf

                        @foreach ($identityQuestions as $question)
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ $question->question }}
                                    @if ($question->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if ($question->field_type === 'text')
                                    @if ($question->question === 'Nama Lengkap')
                                        <input type="text"
                                               class="form-control @error('answers.' . $question->id) is-invalid @enderror"
                                               name="answers[{{ $question->id }}]"
                                               value="{{ old('answers.' . $question->id) }}"
                                               {{ $question->is_required ? 'required' : '' }}>
                                    @elseif (stripos($question->question, 'alamat') !== false)
                                        <!-- Skip address field as per requirement -->
                                        @continue
                                    @else
                                        <input type="text"
                                               class="form-control @error('answers.' . $question->id) is-invalid @enderror"
                                               name="answers[{{ $question->id }}]"
                                               value="{{ old('answers.' . $question->id) }}"
                                               {{ $question->is_required ? 'required' : '' }}>
                                    @endif

                                @elseif ($question->field_type === 'select' || $question->question === 'Pilih Jenis Pelayanan')
                                    @if ($question->question === 'Pilih Jenis Pelayanan')
                                        <select class="form-select @error('answers.' . $question->id) is-invalid @enderror"
                                                name="answers[{{ $question->id }}]"
                                                {{ $question->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih Jenis Pelayanan --</option>
                                            @php
                                                $services = \App\Models\Service::all();
                                            @endphp
                                            @foreach ($services as $service)
                                                <option value="{{ $service->name }}"
                                                        {{ old('answers.' . $question->id) == $service->name ? 'selected' : '' }}>
                                                    {{ $service->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif (stripos($question->question, 'usia') !== false || stripos($question->question, 'Umur') !== false)
                                        <select class="form-select @error('answers.' . $question->id) is-invalid @enderror"
                                                name="answers[{{ $question->id }}]"
                                                {{ $question->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih Usia --</option>
                                            <option value="Dibawah 20 Tahun" {{ old('answers.' . $question->id) == 'Dibawah 20 Tahun' ? 'selected' : '' }}>Dibawah 20 Tahun</option>
                                            <option value="21 s.d 30 Tahun" {{ old('answers.' . $question->id) == '21 s.d 30 Tahun' ? 'selected' : '' }}>21 s.d 30 Tahun</option>
                                            <option value="31 s.d 40 Tahun" {{ old('answers.' . $question->id) == '31 s.d 40 Tahun' ? 'selected' : '' }}>31 s.d 40 Tahun</option>
                                            <option value="41 s.d 50 Tahun" {{ old('answers.' . $question->id) == '41 s.d 50 Tahun' ? 'selected' : '' }}>41 s.d 50 Tahun</option>
                                            <option value="Diatas 50 Tahun" {{ old('answers.' . $question->id) == 'Diatas 50 Tahun' ? 'selected' : '' }}>Diatas 50 Tahun</option>
                                        </select>
                                    @elseif (stripos($question->question, 'pekerjaan') !== false)
                                        <select class="form-select @error('answers.' . $question->id) is-invalid @enderror"
                                                name="answers[{{ $question->id }}]"
                                                {{ $question->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih Pekerjaan --</option>
                                            <option value="PNS/TNI/POLRI" {{ old('answers.' . $question->id) == 'PNS/TNI/POLRI' ? 'selected' : '' }}>PNS/TNI/POLRI</option>
                                            <option value="Pegawai Swasta" {{ old('answers.' . $question->id) == 'Pegawai Swasta' ? 'selected' : '' }}>Pegawai Swasta</option>
                                            <option value="Wiraswasta" {{ old('answers.' . $question->id) == 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                            <option value="Petani/Pekebun" {{ old('answers.' . $question->id) == 'Petani/Pekebun' ? 'selected' : '' }}>Petani/Pekebun</option>
                                            <option value="Pelajar/Mahasiswa" {{ old('answers.' . $question->id) == 'Pelajar/Mahasiswa' ? 'selected' : '' }}>Pelajar/Mahasiswa</option>
                                            <option value="Lainnya" {{ old('answers.' . $question->id) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    @elseif (stripos($question->question, 'pendidikan') !== false)
                                        <select class="form-select @error('answers.' . $question->id) is-invalid @enderror"
                                                name="answers[{{ $question->id }}]"
                                                {{ $question->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih Pendidikan --</option>
                                            <option value="SD" {{ old('answers.' . $question->id) == 'SD' ? 'selected' : '' }}>SD</option>
                                            <option value="SMP" {{ old('answers.' . $question->id) == 'SMP' ? 'selected' : '' }}>SMP</option>
                                            <option value="SMA" {{ old('answers.' . $question->id) == 'SMA' ? 'selected' : '' }}>SMA</option>
                                            <option value="D3" {{ old('answers.' . $question->id) == 'D3' ? 'selected' : '' }}>D3</option>
                                            <option value="D4/S1" {{ old('answers.' . $question->id) == 'D4/S1' ? 'selected' : '' }}>D4/S1</option>
                                            <option value="S2" {{ old('answers.' . $question->id) == 'S2' ? 'selected' : '' }}>S2</option>
                                            <option value="S3" {{ old('answers.' . $question->id) == 'S3' ? 'selected' : '' }}>S3</option>
                                        </select>
                                    @else
                                        <select class="form-select @error('answers.' . $question->id) is-invalid @enderror"
                                                name="answers[{{ $question->id }}]"
                                                {{ $question->is_required ? 'required' : '' }}>
                                            <option value="">-- Pilih {{ $question->question }} --</option>
                                            @foreach ($question->options as $option)
                                                <option value="{{ $option }}"
                                                        {{ old('answers.' . $question->id) == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif

                                @elseif ($question->field_type === 'radio')
                                    @foreach ($question->options as $option)
                                        <div class="form-check">
                                            <input class="form-check-input @error('answers.' . $question->id) is-invalid @enderror"
                                                   type="radio"
                                                   name="answers[{{ $question->id }}]"
                                                   id="q{{ $question->id }}_{{ $loop->index }}"
                                                   value="{{ $option }}"
                                                   {{ old('answers.' . $question->id) == $option ? 'checked' : '' }}
                                                   {{ $question->is_required ? 'required' : '' }}>
                                            <label class="form-check-label" for="q{{ $question->id }}_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach

                                @elseif ($question->field_type === 'number')
                                    <input type="number"
                                           class="form-control @error('answers.' . $question->id) is-invalid @enderror"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ old('answers.' . $question->id) }}"
                                           {{ $question->is_required ? 'required' : '' }}>
                                @elseif ($question->field_type === 'ticket_number')
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control @error('answers.' . $question->id) is-invalid @enderror"
                                               name="answers[{{ $question->id }}]"
                                               placeholder="Masukkan nomor tiket layanan, contoh: LAYANAN-N-2025-001"
                                               value="{{ old('answers.' . $question->id) }}">
                                        <button class="btn btn-outline-secondary" type="button" id="checkTicketBtn">
                                            <i class="fas fa-search"></i> Cek
                                        </button>
                                    </div>
                                    <div id="ticketInfo" class="mt-2"></div>
                                    @error('answers.' . $question->id)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                @endif

                                @error('answers.' . $question->id)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Data Anda aman dan akan dijaga kerahasiaannya
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        padding: 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkTicketBtn = document.getElementById('checkTicketBtn');
    if (checkTicketBtn) {
        checkTicketBtn.addEventListener('click', function() {
            const ticketInput = document.querySelector('input[name*="[answers]"]');
            const ticketNumber = ticketInput ? ticketInput.value.trim() : '';
            const ticketInfoDiv = document.getElementById('ticketInfo');
            
            if (ticketNumber) {
                // Make AJAX request to check ticket
                fetch('/api/check-ticket/' + ticketNumber)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            if (data.has_survey_completed) {
                                ticketInfoDiv.innerHTML = `
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Tiket ini telah menyelesaikan survei.
                                    </div>
                                `;
                                // Disable the form submission
                                document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = true;
                            } else {
                                ticketInfoDiv.innerHTML = `
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Tiket ditemukan: ${data.ticket.service.name} oleh ${data.ticket.user.name}
                                        <br>
                                        Status: ${data.ticket.status}
                                    </div>
                                `;
                                // Re-enable form submission if it was disabled
                                document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = false;
                            }
                        } else {
                            ticketInfoDiv.innerHTML = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Tiket tidak ditemukan
                                </div>
                            `;
                            // Disable the form submission
                            document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = true;
                        }
                    })
                    .catch(error => {
                        ticketInfoDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error saat memeriksa tiket
                            </div>
                        `;
                    });
            }
        });
    }
    
    // Enable form submission by default
    document.getElementById('step1Form').querySelector('button[type="submit"]').disabled = false;
});
</script>
@endpush
