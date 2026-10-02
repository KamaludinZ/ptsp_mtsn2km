@extends('layouts.public')

@section('title', 'Survey Berhasil Dikirim')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center">
                <!-- Success Animation Icon -->
                <div class="success-checkmark mb-4">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>

                <!-- Success Message Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <h2 class="fw-bold text-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            Survey Berhasil Terkirim!
                        </h2>

                        <p class="lead text-muted mb-4">
                            Terima kasih telah meluangkan waktu untuk mengisi survey kami.
                        </p>

                        <div class="alert alert-success border-0 bg-light-success mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Catatan:</strong> Jawaban Anda sangat berarti bagi kami untuk meningkatkan kualitas pelayanan MTsN 2 Kota Malang.
                        </div>

                        <!-- Quick Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 small text-muted">Identitas</p>
                                    <h6 class="mb-0">Lengkap</h6>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-star fa-2x text-success mb-2"></i>
                                    <p class="mb-0 small text-muted">SKM</p>
                                    <h6 class="mb-0">9 Pertanyaan</h6>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                    <p class="mb-0 small text-muted">SPAK</p>
                                    <h6 class="mb-0">10 Pertanyaan</h6>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ route('survey.results') }}" class="btn btn-primary">
                                <i class="fas fa-chart-bar me-2"></i>Lihat Hasil Survey
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Kembali ke Beranda
                            </a>
                        </div>

                        <div class="mt-4">
                            <p class="text-muted small mb-0">
                                <i class="fas fa-calendar-check me-1"></i>
                                Survey dikirim pada: {{ now()->format('d F Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-4">
                    <p class="text-muted small">
                        <i class="fas fa-lock me-1"></i>
                        Data Anda aman dan akan dijaga kerahasiaannya
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .success-checkmark {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    .success-checkmark .check-icon {
        width: 100px;
        height: 100px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #22c55e;
    }
    .success-checkmark .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }
    .success-checkmark .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    .success-checkmark .check-icon::before,
    .success-checkmark .check-icon::after {
        content: '';
        height: 100px;
        position: absolute;
        background: #fff;
        transform: rotate(-45deg);
    }
    .success-checkmark .check-icon .icon-line {
        height: 5px;
        background-color: #22c55e;
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }
    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }
    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }
    .success-checkmark .check-icon .icon-circle {
        top: -4px;
        left: -4px;
        z-index: 10;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        position: absolute;
        box-sizing: content-box;
        border: 4px solid rgba(34, 197, 94, 0.5);
    }
    .success-checkmark .check-icon .icon-fix {
        top: 8px;
        width: 5px;
        left: 26px;
        z-index: 1;
        height: 85px;
        position: absolute;
        transform: rotate(-45deg);
        background-color: #fff;
    }

    @keyframes rotate-circle {
        0% {
            transform: rotate(-45deg);
        }
        5% {
            transform: rotate(-45deg);
        }
        12% {
            transform: rotate(-405deg);
        }
        100% {
            transform: rotate(-405deg);
        }
    }

    @keyframes icon-line-tip {
        0% {
            width: 0;
            left: 1px;
            top: 19px;
        }
        54% {
            width: 0;
            left: 1px;
            top: 19px;
        }
        70% {
            width: 50px;
            left: -8px;
            top: 37px;
        }
        84% {
            width: 17px;
            left: 21px;
            top: 48px;
        }
        100% {
            width: 25px;
            left: 14px;
            top: 46px;
        }
    }

    @keyframes icon-line-long {
        0% {
            width: 0;
            right: 46px;
            top: 54px;
        }
        65% {
            width: 0;
            right: 46px;
            top: 54px;
        }
        84% {
            width: 55px;
            right: 0;
            top: 35px;
        }
        100% {
            width: 47px;
            right: 8px;
            top: 38px;
        }
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }

    .bg-light-success {
        background-color: #f0fdf4 !important;
        color: #166534;
    }

    /* The checkmark animation masks parts of the circle with patches in the page colour */
    [data-theme="dark"] .success-checkmark .check-icon::before,
    [data-theme="dark"] .success-checkmark .check-icon::after,
    [data-theme="dark"] .success-checkmark .check-icon .icon-fix {
        background: #111827;
    }
</style>
@endpush

@push('scripts')
@include('survey.partials.draft')
@endpush
