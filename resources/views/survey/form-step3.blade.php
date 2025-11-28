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
                        Terdapat <strong>10 pertanyaan</strong> terkait praktik anti korupsi.
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

                        <!-- 10 Indeks Persepsi Anti Korupsi (SPAK) Questions -->
                        
                        <!-- Question 1 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">1</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya manipulasi peraturan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.16') is-invalid @enderror"
                                               type="radio"
                                               name="answers[16]"
                                               id="q16_1"
                                               value="Sangat Sering"
                                               {{ old('answers.16') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q16_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.16') is-invalid @enderror"
                                               type="radio"
                                               name="answers[16]"
                                               id="q16_2"
                                               value="Sering"
                                               {{ old('answers.16') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q16_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.16') is-invalid @enderror"
                                               type="radio"
                                               name="answers[16]"
                                               id="q16_3"
                                               value="Jarang"
                                               {{ old('answers.16') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q16_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.16') is-invalid @enderror"
                                               type="radio"
                                               name="answers[16]"
                                               id="q16_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.16') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q16_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.16')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 2 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">2</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menyalahgunakan jabatan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.17') is-invalid @enderror"
                                               type="radio"
                                               name="answers[17]"
                                               id="q17_1"
                                               value="Sangat Sering"
                                               {{ old('answers.17') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q17_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.17') is-invalid @enderror"
                                               type="radio"
                                               name="answers[17]"
                                               id="q17_2"
                                               value="Sering"
                                               {{ old('answers.17') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q17_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.17') is-invalid @enderror"
                                               type="radio"
                                               name="answers[17]"
                                               id="q17_3"
                                               value="Jarang"
                                               {{ old('answers.17') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q17_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.17') is-invalid @enderror"
                                               type="radio"
                                               name="answers[17]"
                                               id="q17_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.17') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q17_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.17')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 3 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">3</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menjual pengaruh di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.18') is-invalid @enderror"
                                               type="radio"
                                               name="answers[18]"
                                               id="q18_1"
                                               value="Sangat Sering"
                                               {{ old('answers.18') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q18_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.18') is-invalid @enderror"
                                               type="radio"
                                               name="answers[18]"
                                               id="q18_2"
                                               value="Sering"
                                               {{ old('answers.18') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q18_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.18') is-invalid @enderror"
                                               type="radio"
                                               name="answers[18]"
                                               id="q18_3"
                                               value="Jarang"
                                               {{ old('answers.18') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q18_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.18') is-invalid @enderror"
                                               type="radio"
                                               name="answers[18]"
                                               id="q18_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.18') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q18_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.18')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 4 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">4</span>
                                Bagaimana menurut Saudara dengan transparansi biaya yang ada di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.19') is-invalid @enderror"
                                               type="radio"
                                               name="answers[19]"
                                               id="q19_1"
                                               value="Tidak Transparan"
                                               {{ old('answers.19') == 'Tidak Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q19_1">
                                            Tidak Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.19') is-invalid @enderror"
                                               type="radio"
                                               name="answers[19]"
                                               id="q19_2"
                                               value="Kurang Transparan"
                                               {{ old('answers.19') == 'Kurang Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q19_2">
                                            Kurang Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.19') is-invalid @enderror"
                                               type="radio"
                                               name="answers[19]"
                                               id="q19_3"
                                               value="Transparan"
                                               {{ old('answers.19') == 'Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q19_3">
                                            Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.19') is-invalid @enderror"
                                               type="radio"
                                               name="answers[19]"
                                               id="q19_4"
                                               value="Sangat Transparan"
                                               {{ old('answers.19') == 'Sangat Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q19_4">
                                            Sangat Transparan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.19')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 5 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">5</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang meminta biaya tambahan diluar ketentuan dan standar pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.20') is-invalid @enderror"
                                               type="radio"
                                               name="answers[20]"
                                               id="q20_1"
                                               value="Sangat Sering"
                                               {{ old('answers.20') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q20_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.20') is-invalid @enderror"
                                               type="radio"
                                               name="answers[20]"
                                               id="q20_2"
                                               value="Sering"
                                               {{ old('answers.20') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q20_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.20') is-invalid @enderror"
                                               type="radio"
                                               name="answers[20]"
                                               id="q20_3"
                                               value="Jarang"
                                               {{ old('answers.20') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q20_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.20') is-invalid @enderror"
                                               type="radio"
                                               name="answers[20]"
                                               id="q20_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.20') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q20_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.20')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 6 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">6</span>
                                Apakah Saudara pernah mengetahui adanya pemberian hadiah kepada petugas di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.21') is-invalid @enderror"
                                               type="radio"
                                               name="answers[21]"
                                               id="q21_1"
                                               value="Sangat Sering"
                                               {{ old('answers.21') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q21_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.21') is-invalid @enderror"
                                               type="radio"
                                               name="answers[21]"
                                               id="q21_2"
                                               value="Sering"
                                               {{ old('answers.21') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q21_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.21') is-invalid @enderror"
                                               type="radio"
                                               name="answers[21]"
                                               id="q21_3"
                                               value="Jarang"
                                               {{ old('answers.21') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q21_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.21') is-invalid @enderror"
                                               type="radio"
                                               name="answers[21]"
                                               id="q21_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.21') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q21_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.21')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 7 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">7</span>
                                Bagaimana menurut Saudara dengan transparansi transaksi pembayaran yang ada di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.22') is-invalid @enderror"
                                               type="radio"
                                               name="answers[22]"
                                               id="q22_1"
                                               value="Tidak Transparan"
                                               {{ old('answers.22') == 'Tidak Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q22_1">
                                            Tidak Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.22') is-invalid @enderror"
                                               type="radio"
                                               name="answers[22]"
                                               id="q22_2"
                                               value="Kurang Transparan"
                                               {{ old('answers.22') == 'Kurang Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q22_2">
                                            Kurang Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.22') is-invalid @enderror"
                                               type="radio"
                                               name="answers[22]"
                                               id="q22_3"
                                               value="Transparan"
                                               {{ old('answers.22') == 'Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q22_3">
                                            Transparan
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.22') is-invalid @enderror"
                                               type="radio"
                                               name="answers[22]"
                                               id="q22_4"
                                               value="Sangat Transparan"
                                               {{ old('answers.22') == 'Sangat Transparan' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q22_4">
                                            Sangat Transparan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.22')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 8 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">8</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan praktik percaloan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.23') is-invalid @enderror"
                                               type="radio"
                                               name="answers[23]"
                                               id="q23_1"
                                               value="Sangat Sering"
                                               {{ old('answers.23') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q23_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.23') is-invalid @enderror"
                                               type="radio"
                                               name="answers[23]"
                                               id="q23_2"
                                               value="Sering"
                                               {{ old('answers.23') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q23_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.23') is-invalid @enderror"
                                               type="radio"
                                               name="answers[23]"
                                               id="q23_3"
                                               value="Jarang"
                                               {{ old('answers.23') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q23_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.23') is-invalid @enderror"
                                               type="radio"
                                               name="answers[23]"
                                               id="q23_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.23') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q23_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.23')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 9 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">9</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan kecurangan dalam pelayanan di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.24') is-invalid @enderror"
                                               type="radio"
                                               name="answers[24]"
                                               id="q24_1"
                                               value="Sangat Sering"
                                               {{ old('answers.24') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q24_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.24') is-invalid @enderror"
                                               type="radio"
                                               name="answers[24]"
                                               id="q24_2"
                                               value="Sering"
                                               {{ old('answers.24') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q24_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.24') is-invalid @enderror"
                                               type="radio"
                                               name="answers[24]"
                                               id="q24_3"
                                               value="Jarang"
                                               {{ old('answers.24') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q24_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.24') is-invalid @enderror"
                                               type="radio"
                                               name="answers[24]"
                                               id="q24_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.24') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q24_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.24')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Question 10 -->
                        <div class="question-card mb-4 p-4 border rounded bg-light">
                            <h6 class="fw-semibold mb-3">
                                <span class="badge bg-warning text-dark me-2">10</span>
                                Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan transaksi rahasia dalam melayani di MTsN 2 Kota Malang?
                                <span class="text-danger">*</span>
                            </h6>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.25') is-invalid @enderror"
                                               type="radio"
                                               name="answers[25]"
                                               id="q25_1"
                                               value="Sangat Sering"
                                               {{ old('answers.25') == 'Sangat Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q25_1">
                                            Sangat Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.25') is-invalid @enderror"
                                               type="radio"
                                               name="answers[25]"
                                               id="q25_2"
                                               value="Sering"
                                               {{ old('answers.25') == 'Sering' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q25_2">
                                            Sering
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.25') is-invalid @enderror"
                                               type="radio"
                                               name="answers[25]"
                                               id="q25_3"
                                               value="Jarang"
                                               {{ old('answers.25') == 'Jarang' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q25_3">
                                            Jarang
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-card">
                                        <input class="form-check-input @error('answers.25') is-invalid @enderror"
                                               type="radio"
                                               name="answers[25]"
                                               id="q25_4"
                                               value="Tidak Pernah"
                                               {{ old('answers.25') == 'Tidak Pernah' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100 p-3 border rounded cursor-pointer"
                                               for="q25_4">
                                            Tidak Pernah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @error('answers.25')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
