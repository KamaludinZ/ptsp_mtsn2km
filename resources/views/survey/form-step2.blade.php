@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat - SKM')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Survey Kepuasan Masyarakat (SKM)
                </h2>
                @include('survey.partials.edition')
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 2 dari 3</span>
                    <span class="text-muted small">66%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 66%;"
                         aria-valuenow="66" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Tahap 2: Survei Kepuasan Masyarakat (SKM)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Petunjuk:</strong> Berikan penilaian Anda terhadap kualitas pelayanan di MTsN 2 Kota Malang.
                        Terdapat <strong>{{ $skmQuestions->count() }} pertanyaan</strong> dengan 4 pilihan jawaban.
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

                    <form action="{{ route('survey.step2.store') }}" method="POST" id="step2Form">
                        @csrf
                        <p class="small text-muted mb-3"><span class="text-danger fw-bold">*</span> wajib diisi</p>

                        @include('survey.partials.rating-questions', ['questions' => $skmQuestions, 'accent' => 'success'])

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('survey.form') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-success">
                                Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
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
        background: #f1f5f9;
        border-color: #3b82f6;
    }
    .form-check-input:checked + label {
        background: #dbeafe !important;
        border-color: #3b82f6 !important;
        font-weight: 600;
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush
