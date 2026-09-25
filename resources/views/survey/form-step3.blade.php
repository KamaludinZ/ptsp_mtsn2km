@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat - SPAK')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-warning">
                    <i class="fas fa-shield-alt me-2"></i>
                    Survei Persepsi Anti Korupsi (SPAK)
                </h2>
                <p class="text-muted">MTsN 2 Kota Malang</p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 3 dari 3 - Tahap Terakhir!</span>
                    <span class="text-muted small">100%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-gavel me-2"></i>
                        Tahap 3: Survei Persepsi Anti Korupsi (SPAK)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tahap Terakhir!</strong> Berikan penilaian Anda tentang transparansi dan integritas pelayanan.
                        Terdapat <strong>{{ $spakQuestions->count() }} pertanyaan</strong> terkait praktik anti korupsi.
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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

                    <form action="{{ route('survey.step3.store') }}" method="POST" id="step3Form">
                        @csrf

                        @include('survey.partials.rating-questions', ['questions' => $spakQuestions, 'accent' => 'warning'])

                        
                        <!-- SPAK Suggestions Box -->
                        <div class="mt-4">
                            <label for="spak_suggestions" class="form-label fw-semibold">Saran dan Masukan Tambahan</label>
                            <textarea 
                                name="spak_suggestions" 
                                id="spak_suggestions" 
                                class="form-control @error('spak_suggestions') is-invalid @enderror" 
                                rows="4" 
                                placeholder="Tulis saran atau masukan untuk peningkatan integritas dan pencegahan korupsi...">{{ old('spak_suggestions') }}</textarea>
                            @error('spak_suggestions')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Saran dan masukan Anda sangat membantu dalam meningkatkan integritas pelayanan.</small>
                        </div>

                        <div class="alert alert-success mt-4">
                            <i class="fas fa-check-circle me-2"></i>
                            Setelah menyelesaikan tahap ini, survey Anda akan langsung tersimpan. Terima kasih atas partisipasi Anda!
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('survey.step2') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Survey
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Privacy Notice -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-lock me-1"></i>
                    Semua jawaban Anda bersifat anonim dan hanya digunakan untuk perbaikan layanan
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
        border-bottom: 3px solid rgba(0, 0, 0, 0.1);
    }
    .question-card {
        background: #f8f9fa;
        border: 2px solid #e5e7eb !important;
        border-radius: 12px !important;
        transition: all 0.3s ease;
    }
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .form-check-card label {
        background: white;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .form-check-card label:hover {
        background: #fff7ed;
        border-color: #fbbf24;
    }
    .form-check-input:checked + label {
        background: #fef3c7 !important;
        border-color: #fbbf24 !important;
        font-weight: 600;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush
