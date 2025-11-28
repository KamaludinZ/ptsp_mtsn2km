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
                <p class="text-muted">MTsN 2 Kota Malang</p>
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
                        Terdapat <strong>9 pertanyaan</strong> dengan 4 pilihan jawaban.
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

                        <!-- 9 Survei Kepuasan Masyarakat (SKM) Questions -->
                        
                        <!-- Question 1 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">1</span>
                                Bagaimana pendapat Saudara tentang kesesuaian persyaratan layanan di MTsN 2 Kota Malang dengan jenis pelayanannya?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.7') is-invalid @enderror"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_1"
                                               value="Tidak Sesuai"
                                               {{ old('answers.7') == 'Tidak Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_1">
                                            Tidak Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.7') is-invalid @enderror"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_2"
                                               value="Kurang Sesuai"
                                               {{ old('answers.7') == 'Kurang Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_2">
                                            Kurang Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.7') is-invalid @enderror"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_3"
                                               value="Sesuai"
                                               {{ old('answers.7') == 'Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_3">
                                            Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.7') is-invalid @enderror"
                                               type="radio"
                                               name="answers[7]"
                                               id="q7_4"
                                               value="Sangat Sesuai"
                                               {{ old('answers.7') == 'Sangat Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q7_4">
                                            Sangat Sesuai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.7')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 2 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">2</span>
                                Bagaimana pendapat Saudara tentang kemudahan prosedur pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.8') is-invalid @enderror"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_1"
                                               value="Tidak Mudah"
                                               {{ old('answers.8') == 'Tidak Mudah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_1">
                                            Tidak Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.8') is-invalid @enderror"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_2"
                                               value="Kurang Mudah"
                                               {{ old('answers.8') == 'Kurang Mudah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_2">
                                            Kurang Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.8') is-invalid @enderror"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_3"
                                               value="Mudah"
                                               {{ old('answers.8') == 'Mudah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_3">
                                            Mudah
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.8') is-invalid @enderror"
                                               type="radio"
                                               name="answers[8]"
                                               id="q8_4"
                                               value="Sangat Mudah"
                                               {{ old('answers.8') == 'Sangat Mudah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q8_4">
                                            Sangat Mudah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.8')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 3 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">3</span>
                                Bagaimana pendapat Saudara tentang kecepatan pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.9') is-invalid @enderror"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_1"
                                               value="Tidak Cepat"
                                               {{ old('answers.9') == 'Tidak Cepat' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_1">
                                            Tidak Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.9') is-invalid @enderror"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_2"
                                               value="Kurang Cepat"
                                               {{ old('answers.9') == 'Kurang Cepat' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_2">
                                            Kurang Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.9') is-invalid @enderror"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_3"
                                               value="Cepat"
                                               {{ old('answers.9') == 'Cepat' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_3">
                                            Cepat
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.9') is-invalid @enderror"
                                               type="radio"
                                               name="answers[9]"
                                               id="q9_4"
                                               value="Sangat Cepat"
                                               {{ old('answers.9') == 'Sangat Cepat' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q9_4">
                                            Sangat Cepat
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.9')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 4 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">4</span>
                                Bagaimana pendapat Saudara tentang Jenis pelayanan ini di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.10') is-invalid @enderror"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_1"
                                               value="Tidak Bagus"
                                               {{ old('answers.10') == 'Tidak Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_1">
                                            Tidak Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.10') is-invalid @enderror"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_2"
                                               value="Kurang Bagus"
                                               {{ old('answers.10') == 'Kurang Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_2">
                                            Kurang Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.10') is-invalid @enderror"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_3"
                                               value="Bagus"
                                               {{ old('answers.10') == 'Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_3">
                                            Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.10') is-invalid @enderror"
                                               type="radio"
                                               name="answers[10]"
                                               id="q10_4"
                                               value="Sangat Bagus"
                                               {{ old('answers.10') == 'Sangat Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q10_4">
                                            Sangat Bagus
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.10')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 5 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">5</span>
                                Bagaimana pendapat Saudara tentang kemampuan petugas dalam memberikan pelayanan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.11') is-invalid @enderror"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_1"
                                               value="Tidak Mampu"
                                               {{ old('answers.11') == 'Tidak Mampu' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_1">
                                            Tidak Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.11') is-invalid @enderror"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_2"
                                               value="Kurang Mampu"
                                               {{ old('answers.11') == 'Kurang Mampu' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_2">
                                            Kurang Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.11') is-invalid @enderror"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_3"
                                               value="Mampu"
                                               {{ old('answers.11') == 'Mampu' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_3">
                                            Mampu
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.11') is-invalid @enderror"
                                               type="radio"
                                               name="answers[11]"
                                               id="q11_4"
                                               value="Sangat Mampu"
                                               {{ old('answers.11') == 'Sangat Mampu' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q11_4">
                                            Sangat Mampu
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.11')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 6 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">6</span>
                                Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas dalam memberikan pelayanan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.12') is-invalid @enderror"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_1"
                                               value="Tidak Sopan"
                                               {{ old('answers.12') == 'Tidak Sopan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_1">
                                            Tidak Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.12') is-invalid @enderror"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_2"
                                               value="Kurang Sopan"
                                               {{ old('answers.12') == 'Kurang Sopan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_2">
                                            Kurang Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.12') is-invalid @enderror"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_3"
                                               value="Sopan"
                                               {{ old('answers.12') == 'Sopan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_3">
                                            Sopan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.12') is-invalid @enderror"
                                               type="radio"
                                               name="answers[12]"
                                               id="q12_4"
                                               value="Sangat Sopan"
                                               {{ old('answers.12') == 'Sangat Sopan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q12_4">
                                            Sangat Sopan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.12')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 7 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">7</span>
                                Bagaimana pendapat Saudara tentang maklumat pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.13') is-invalid @enderror"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_1"
                                               value="Tidak Jelas"
                                               {{ old('answers.13') == 'Tidak Jelas' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_1">
                                            Tidak Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.13') is-invalid @enderror"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_2"
                                               value="Kurang Jelas"
                                               {{ old('answers.13') == 'Kurang Jelas' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_2">
                                            Kurang Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.13') is-invalid @enderror"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_3"
                                               value="Jelas"
                                               {{ old('answers.13') == 'Jelas' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_3">
                                            Jelas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.13') is-invalid @enderror"
                                               type="radio"
                                               name="answers[13]"
                                               id="q13_4"
                                               value="Sangat Jelas"
                                               {{ old('answers.13') == 'Sangat Jelas' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q13_4">
                                            Sangat Jelas
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.13')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 8 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">8</span>
                                Bagaimana pendapat Saudara tentang penanganan pengaduan, saran dan masukan?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.14') is-invalid @enderror"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_1"
                                               value="Tidak Bagus"
                                               {{ old('answers.14') == 'Tidak Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_1">
                                            Tidak Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.14') is-invalid @enderror"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_2"
                                               value="Kurang Bagus"
                                               {{ old('answers.14') == 'Kurang Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_2">
                                            Kurang Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.14') is-invalid @enderror"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_3"
                                               value="Bagus"
                                               {{ old('answers.14') == 'Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_3">
                                            Bagus
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.14') is-invalid @enderror"
                                               type="radio"
                                               name="answers[14]"
                                               id="q14_4"
                                               value="Sangat Bagus"
                                               {{ old('answers.14') == 'Sangat Bagus' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q14_4">
                                            Sangat Bagus
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.14')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 9 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-primary me-2">9</span>
                                Bagaimana pendapat Saudara tentang kesesuaian biaya pelayanan dengan standar pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.15') is-invalid @enderror"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_1"
                                               value="Selalu Tidak Sesuai"
                                               {{ old('answers.15') == 'Selalu Tidak Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_1">
                                            Selalu Tidak Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.15') is-invalid @enderror"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_2"
                                               value="Kadang-kadang Sesuai"
                                               {{ old('answers.15') == 'Kadang-kadang Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_2">
                                            Kadang-kadang Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.15') is-invalid @enderror"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_3"
                                               value="Sesuai"
                                               {{ old('answers.15') == 'Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_3">
                                            Sesuai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.15') is-invalid @enderror"
                                               type="radio"
                                               name="answers[15]"
                                               id="q15_4"
                                               value="Selalu Sesuai"
                                               {{ old('answers.15') == 'Selalu Sesuai' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q15_4">
                                            Selalu Sesuai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.15')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

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
