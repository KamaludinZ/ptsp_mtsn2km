@extends('layouts.public')

@section('title', 'Survei Kepuasan Masyarakat - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
<style>
    /* Progress bar styling */
    .progress-step {
        font-size: 12px;
        font-weight: 600;
        color: #9ca3af;
    }
    
    .progress-step.active {
        color: #f59e0b;
        font-weight: 700;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }
    
    .step:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-color: #d1d5db;
        z-index: -1;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .step.active .step-number {
        background-color: #f59e0b;
        color: white;
    }
    
    .step.completed .step-number {
        background-color: #22c55e;
        color: white;
    }
    
    .survey-step {
        display: none;
    }
    
    .survey-step.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .card {
        border-radius: 12px;
    }

    .form-check-input:checked {
        background-color: #f59e0b;
        border-color: #f59e0b;
    }
    
    .btn-nav {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .bg-amber {
        background-color: #f59e0b;
    }
    
    .text-amber {
        color: #f59e0b;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="h3 fw-bold mb-3">📋 Survei Kepuasan Masyarakat & Anti Korupsi</h1>
                @if($activeEdition)
                    <p class="text-muted">{{ $activeEdition->name }}</p>
                @else
                    <p class="text-muted">Periode Survei Belum Ditentukan</p>
                @endif
                
                <div class="step-indicator">
                    <div class="step active" id="step1-indicator">
                        <div class="step-number">1</div>
                        <div class="progress-step active">Identitas</div>
                    </div>
                    <div class="step" id="step2-indicator">
                        <div class="step-number">2</div>
                        <div class="progress-step">SKM</div>
                    </div>
                    <div class="step" id="step3-indicator">
                        <div class="step-number">3</div>
                        <div class="progress-step">SPAK</div>
                    </div>
                </div>
            </div>

            <!-- Survey Form -->
            <form id="surveyForm" action="{{ route('supervision.skm.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="survey_type" value="skm">
                
                <!-- Step 1: Identity Form -->
                <div id="step1" class="survey-step active">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-amber text-white">
                            <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Form Data Diri Responden</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Service Type Selection -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Pilih Jenis Pelayanan <span class="text-danger">*</span></label>
                                    <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis Pelayanan --</option>
                                        <option value="mutasi_siswa_masuk" {{ old('service_type') == 'mutasi_siswa_masuk' ? 'selected' : '' }}>Mutasi Siswa Masuk</option>
                                        <option value="mutasi_siswa_keluar" {{ old('service_type') == 'mutasi_siswa_keluar' ? 'selected' : '' }}>Mutasi Siswa Keluar</option>
                                        <option value="rekomendasi_siswa" {{ old('service_type') == 'rekomendasi_siswa' ? 'selected' : '' }}>Penerbitan Surat Rekomendasi Siswa</option>
                                        <option value="ppdb" {{ old('service_type') == 'ppdb' ? 'selected' : '' }}>Penerimaan Peserta Didik Baru</option>
                                        <option value="penelitian_observasi" {{ old('service_type') == 'penelitian_observasi' ? 'selected' : '' }}>Izin Melaksanakan Penelitian / Observasi</option>
                                        <option value="selesai_penelitian" {{ old('service_type') == 'selesai_penelitian' ? 'selected' : '' }}>Selesai Melaksanakan Penelitian/Observasi</option>
                                        <option value="keterangan_rusak_ijazah" {{ old('service_type') == 'keterangan_rusak_ijazah' ? 'selected' : '' }}>Surat Keterangan Kerusakan Ijazah</option>
                                        <option value="keterangan_hilang_ijazah" {{ old('service_type') == 'keterangan_hilang_ijazah' ? 'selected' : '' }}>Surat Keterangan Pengganti Ijazah Hilang</option>
                                        <option value="legalisasi_ijazah" {{ old('service_type') == 'legalisasi_ijazah' ? 'selected' : '' }}>Legalisasi Ijazah Offline</option>
                                        <option value="ambil_ijazah" {{ old('service_type') == 'ambil_ijazah' ? 'selected' : '' }}>Pengambilan Ijazah</option>
                                        <option value="perbaikan_nama_ijazah" {{ old('service_type') == 'perbaikan_nama_ijazah' ? 'selected' : '' }}>Perbaikan Kesalahan Penulisan Ijazah</option>
                                        <option value="tamu_studi_banding" {{ old('service_type') == 'tamu_studi_banding' ? 'selected' : '' }}>Penerimaan Tamu Studi Banding</option>
                                        <option value="tamu_dinas" {{ old('service_type') == 'tamu_dinas' ? 'selected' : '' }}>Penerimaan Tamu Dinas</option>
                                        <option value="pengaduan_masyarakat" {{ old('service_type') == 'pengaduan_masyarakat' ? 'selected' : '' }}>Pengaduan Masyarakat</option>
                                        <option value="kerjasama_wartawan" {{ old('service_type') == 'kerjasama_wartawan' ? 'selected' : '' }}>SOP Kerjasama dengan Wartawan</option>
                                        <option value="screening_kesehatan" {{ old('service_type') == 'screening_kesehatan' ? 'selected' : '' }}>Screening Kesehatan Siswa</option>
                                        <option value="penerimaan_iuran_komite" {{ old('service_type') == 'penerimaan_iuran_komite' ? 'selected' : '' }}>Penerimaan Iuran Komite</option>
                                    </select>
                                    @error('service_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Usia <span class="text-danger">*</span></label>
                                    <select name="age" class="form-select @error('age') is-invalid @enderror" required>
                                        <option value="">-- Pilih Usia --</option>
                                        <option value="<20" {{ old('age') == '<20' ? 'selected' : '' }}>Dibawah 20 Tahun</option>
                                        <option value="21-30" {{ old('age') == '21-30' ? 'selected' : '' }}>21 s.d 30 Tahun</option>
                                        <option value="31-40" {{ old('age') == '31-40' ? 'selected' : '' }}>31 s.d 40 Tahun</option>
                                        <option value="41-50" {{ old('age') == '41-50' ? 'selected' : '' }}>41 s.d 50 Tahun</option>
                                        <option value=">50" {{ old('age') == '>50' ? 'selected' : '' }}>Diatas 50 Tahun</option>
                                    </select>
                                    @error('age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pendidikan <span class="text-danger">*</span></label>
                                    <select name="education" class="form-select @error('education') is-invalid @enderror" required>
                                        <option value="">-- Pilih Pendidikan --</option>
                                        <option value="sd" {{ old('education') == 'sd' ? 'selected' : '' }}>SD</option>
                                        <option value="smp" {{ old('education') == 'smp' ? 'selected' : '' }}>SMP</option>
                                        <option value="sma" {{ old('education') == 'sma' ? 'selected' : '' }}>SMA</option>
                                        <option value="d3" {{ old('education') == 'd3' ? 'selected' : '' }}>D3</option>
                                        <option value="d4/s1" {{ old('education') == 'd4/s1' ? 'selected' : '' }}>D4/S1</option>
                                        <option value="s2" {{ old('education') == 's2' ? 'selected' : '' }}>S2</option>
                                        <option value="s3" {{ old('education') == 's3' ? 'selected' : '' }}>S3</option>
                                    </select>
                                    @error('education')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pekerjaan <span class="text-danger">*</span></label>
                                    <select name="occupation" class="form-select @error('occupation') is-invalid @enderror" required>
                                        <option value="">-- Pilih Pekerjaan --</option>
                                        <option value="pns/tni/polri" {{ old('occupation') == 'pns/tni/polri' ? 'selected' : '' }}>PNS/TNI/POLRI</option>
                                        <option value="pegawai_swasta" {{ old('occupation') == 'pegawai_swasta' ? 'selected' : '' }}>Pegawai Swasta</option>
                                        <option value="wiraswasta" {{ old('occupation') == 'wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                        <option value="petani/pekebun" {{ old('occupation') == 'petani/pekebun' ? 'selected' : '' }}>Petani/Pekebun</option>
                                        <option value="pelajar/mahasiswa" {{ old('occupation') == 'pelajar/mahasiswa' ? 'selected' : '' }}>Pelajar/Mahasiswa</option>
                                        <option value="lainnya" {{ old('occupation') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('occupation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">No. Telepon</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kode Tiket Layanan <span class="text-danger">*</span></label>
                                    <input type="text" id="ticket_code" name="ticket_code" class="form-control @error('ticket_code') is-invalid @enderror"
                                           value="{{ old('ticket_code') }}" placeholder="Contoh: N-202511-001" required>
                                    <div class="form-text">Masukkan kode tiket layanan yang Anda terima</div>
                                    @error('ticket_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="ticket_validation_message" class="mt-2"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Aktif <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" placeholder="email@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep(2)">Lanjut ke SKM <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: SKM Questions -->
                <div id="step2" class="survey-step">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-smile me-2"></i>Data Pertanyaan Survei Kepuasan Masyarakat</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Silakan berikan penilaian Anda terhadap pelayanan di MTsN 2 Kota Malang
                            </div>
                            
                            <div class="row g-4">
                                <!-- Question 1 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">1. Bagaimana pendapat Saudara tentang kesesuaian persyaratan layanan di MTsN 2 Kota Malang dengan jenis pelayanannya.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[0]" value="1" required>
                                                <label class="form-check-label">Tidak Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[0]" value="2" required>
                                                <label class="form-check-label">Kurang Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[0]" value="3" required>
                                                <label class="form-check-label">Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[0]" value="4" required>
                                                <label class="form-check-label">Sangat Sesuai</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 2 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">2. Bagaimana pemahaman Saudara tentang kemudahan prosedur pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[1]" value="1" required>
                                                <label class="form-check-label">Tidak Mudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[1]" value="2" required>
                                                <label class="form-check-label">Kurang Mudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[1]" value="3" required>
                                                <label class="form-check-label">Mudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[1]" value="4" required>
                                                <label class="form-check-label">Sangat Mudah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 3 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">3. Bagaimana pendapat Saudara tentang kecepatan pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[2]" value="1" required>
                                                <label class="form-check-label">Tidak Cepat</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[2]" value="2" required>
                                                <label class="form-check-label">Kurang Cepat</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[2]" value="3" required>
                                                <label class="form-check-label">Cepat</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[2]" value="4" required>
                                                <label class="form-check-label">Sangat Cepat</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 4 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">4. Bagaimana pendapat Saudara tentang Jenis pelayanan ini di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[3]" value="1" required>
                                                <label class="form-check-label">Tidak Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[3]" value="2" required>
                                                <label class="form-check-label">Kurang Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[3]" value="3" required>
                                                <label class="form-check-label">Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[3]" value="4" required>
                                                <label class="form-check-label">Sangat Bagus</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 5 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">5. Bagaimana pendapat Saudara tentang kemampuan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[4]" value="1" required>
                                                <label class="form-check-label">Tidak Mampu</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[4]" value="2" required>
                                                <label class="form-check-label">Kurang Mampu</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[4]" value="3" required>
                                                <label class="form-check-label">Mampu</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[4]" value="4" required>
                                                <label class="form-check-label">Sangat Mampu</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 6 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">6. Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[5]" value="1" required>
                                                <label class="form-check-label">Tidak Sopan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[5]" value="2" required>
                                                <label class="form-check-label">Kurang Sopan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[5]" value="3" required>
                                                <label class="form-check-label">Sopan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[5]" value="4" required>
                                                <label class="form-check-label">Sangat Sopan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 7 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">7. Bagaimana pendapat Saudara tentang maklumat pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[6]" value="1" required>
                                                <label class="form-check-label">Tidak Jelas</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[6]" value="2" required>
                                                <label class="form-check-label">Kurang Jelas</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[6]" value="3" required>
                                                <label class="form-check-label">Jelas</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[6]" value="4" required>
                                                <label class="form-check-label">Sangat Jelas</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 8 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">8. Penanganan Pengaduan, Saran dan Masukan - Bagaimana pendapat Saudara tentang Sarana dan Penanganan atas Pengaduan, Kritik dan Saran pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[7]" value="1" required>
                                                <label class="form-check-label">Tidak Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[7]" value="2" required>
                                                <label class="form-check-label">Kurang Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[7]" value="3" required>
                                                <label class="form-check-label">Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[7]" value="4" required>
                                                <label class="form-check-label">Sangat Bagus</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 9 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">9. Kesesuaian Biaya Pelayanan - Bagaimana pendapat Saudara tentang kesesuaian antara biaya pelayanan dengan yang ada pada standar pelayanan di MTsN 2 Kota Malang (semua jenis layanan gratis).</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[8]" value="1" required>
                                                <label class="form-check-label">Selalu Tidak Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[8]" value="2" required>
                                                <label class="form-check-label">Terkadang Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[8]" value="3" required>
                                                <label class="form-check-label">Sesuai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="skm_answers[8]" value="4" required>
                                                <label class="form-check-label">Selalu Sesuai</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary btn-lg px-5" onclick="prevStep(1)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
                                <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep(3)">Lanjut ke SPAK <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: SPAK Questions -->
                <div id="step3" class="survey-step">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Data Pertanyaan Indeks Persepsi Anti Korupsi</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Tolong berikan penilaian yang objektif untuk pencegahan korupsi di lingkungan MTsN 2 Kota Malang
                            </div>
                            
                            <div class="row g-4">
                                <!-- Question 1 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">1. Apakah Saudara pernah mengalami atau mengetahui adanya manipulasi peraturan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[0]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[0]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[0]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[0]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 2 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">2. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menyalahgunaan jabatan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[1]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[1]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[1]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[1]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 3 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">3. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menjual pengaruh di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[2]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[2]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[2]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[2]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 4 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">4. Bagaimana menurut Saudara dengan transparansi biaya yang ada di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[3]" value="tidak_transparan" required>
                                                <label class="form-check-label">Tidak Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[3]" value="kurang_transparan" required>
                                                <label class="form-check-label">Kurang Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[3]" value="transparan" required>
                                                <label class="form-check-label">Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[3]" value="sangat_transparan" required>
                                                <label class="form-check-label">Sangat Transparan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 5 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">5. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang meminta biaya tambahan diluar ketentuan dan standar pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[4]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[4]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[4]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[4]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 6 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">6. Apakah Saudara pernah mengetahui adanya pemberian hadiah kepada petugas di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[5]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[5]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[5]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[5]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 7 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">7. Bagaimana menurut Saudara dengan transparansi transaksi pembayaran yang ada di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[6]" value="tidak_transparan" required>
                                                <label class="form-check-label">Tidak Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[6]" value="kurang_transparan" required>
                                                <label class="form-check-label">Kurang Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[6]" value="transparan" required>
                                                <label class="form-check-label">Transparan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[6]" value="sangat_transparan" required>
                                                <label class="form-check-label">Sangat Transparan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 8 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">8. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan praktik percaloan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[7]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[7]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[7]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[7]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 9 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">9. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan kecurangan dalam pelayanan di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[8]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[8]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[8]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[8]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Question 10 -->
                                <div class="col-12">
                                    <div class="card p-4 bg-light">
                                        <label class="form-label fw-semibold">10. Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan transaksi rahasia dalam melayani di MTsN 2 Kota Malang.</label>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[9]" value="sangat_sering" required>
                                                <label class="form-check-label">Sangat Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[9]" value="sering" required>
                                                <label class="form-check-label">Sering</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[9]" value="jarang" required>
                                                <label class="form-check-label">Jarang</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="spak_answers[9]" value="tidak_pernah" required>
                                                <label class="form-check-label">Tidak Pernah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- SPAK Suggestions -->
                                <div class="col-12 mt-4">
                                    <div class="card p-4">
                                        <label class="form-label fw-semibold">Saran & Masukan (Opsional)</label>
                                        <textarea name="spak_suggestions" class="form-control" rows="4" placeholder="Tulis saran atau masukan untuk pencegahan korupsi di MTsN 2 Kota Malang..."></textarea>
                                        <div class="form-text">Saran Anda sangat membantu dalam meningkatkan integritas pelayanan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary btn-lg px-5" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
                                <button type="submit" class="btn btn-success btn-lg px-5"><i class="fas fa-paper-plane me-2"></i>Kirim Survei</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let ticketCodeValid = false;

// Validate ticket code via AJAX
document.getElementById('ticket_code').addEventListener('blur', function() {
    const ticketCode = this.value.trim();
    const messageDiv = document.getElementById('ticket_validation_message');

    if (!ticketCode) {
        messageDiv.innerHTML = '';
        ticketCodeValid = false;
        return;
    }

    // Show loading
    messageDiv.innerHTML = '<div class="alert alert-info py-2"><i class="fas fa-spinner fa-spin me-2"></i>Memeriksa kode tiket...</div>';

    // Check ticket code
    fetch('{{ route("supervision.skm.validate-ticket") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ticket_code: ticketCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            messageDiv.innerHTML = '<div class="alert alert-success py-2"><i class="fas fa-check-circle me-2"></i>' + data.message + '</div>';
            ticketCodeValid = true;
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger py-2"><i class="fas fa-times-circle me-2"></i>' + data.message + '</div>';
            ticketCodeValid = false;
        }
    })
    .catch(error => {
        messageDiv.innerHTML = '<div class="alert alert-warning py-2"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memvalidasi kode tiket. Silakan coba lagi.</div>';
        ticketCodeValid = false;
    });
});

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.survey-step').forEach(element => {
        element.classList.remove('active');
    });
    
    // Show current step
    document.getElementById('step' + step).classList.add('active');
    
    // Update step indicators
    document.querySelectorAll('.step').forEach((element, index) => {
        element.classList.remove('active', 'completed');
        if (index + 1 < step) {
            element.classList.add('completed');
        } else if (index + 1 === step) {
            element.classList.add('active');
        }
    });
    
    currentStep = step;
}

function nextStep(step) {
    // Validate current step before proceeding
    if (currentStep === 1) {
        // Validate identity form
        const serviceType = document.querySelector('select[name="service_type"]').value;
        const name = document.querySelector('input[name="name"]').value;
        const age = document.querySelector('select[name="age"]').value;
        const gender = document.querySelector('select[name="gender"]').value;
        const education = document.querySelector('select[name="education"]').value;
        const occupation = document.querySelector('select[name="occupation"]').value;
        const ticketCode = document.querySelector('input[name="ticket_code"]').value;
        const email = document.querySelector('input[name="email"]').value;

        if (!serviceType || !name || !age || !gender || !education || !occupation || !ticketCode || !email) {
            alert('Mohon lengkapi semua data identitas terlebih dahulu!');
            return false;
        }

        // Check if ticket code is valid
        if (!ticketCodeValid) {
            alert('Kode tiket tidak valid atau sudah digunakan untuk survei. Mohon periksa kembali kode tiket Anda.');
            document.getElementById('ticket_code').focus();
            return false;
        }
    } else if (currentStep === 2) {
        // Validate SKM answers
        const skmQuestions = document.querySelectorAll('input[name^="skm_answers["]:checked');
        
        // Count unique questions answered (skm_answers[0], skm_answers[1], etc.)
        const answeredQuestions = new Set();
        skmQuestions.forEach(radio => {
            const name = radio.name;
            const questionIndex = name.match(/skm_answers\[(\d+)\]/);
            if (questionIndex) {
                answeredQuestions.add(questionIndex[1]);
            }
        });
        
        if (answeredQuestions.size < 9) {
            alert('Mohon jawab semua pertanyaan Survei Kepuasan Masyarakat!');
            return false;
        }
    }
    
    if (step <= 3) {
        showStep(step);
    }
}

function prevStep(step) {
    if (step >= 1) {
        showStep(step);
    }
}

// Initialize first step
showStep(currentStep);

// Initialize form submission
document.getElementById('surveyForm').addEventListener('submit', function(e) {
    // Validate SPAK answers before submitting
    const spakQuestions = document.querySelectorAll('input[name^="spak_answers["]');
    
    // Count unique SPAK questions that have been answered
    const answeredSpakQuestions = new Set();
    spakQuestions.forEach(radio => {
        if (radio.checked) {
            const name = radio.name;
            const questionIndex = name.match(/spak_answers\[(\d+)\]/);
            if (questionIndex) {
                answeredSpakQuestions.add(questionIndex[1]);
            }
        }
    });
    
    if (answeredSpakQuestions.size < 10) {
        alert('Mohon jawab semua 10 pertanyaan Survei Persepsi Anti Korupsi!');
        e.preventDefault();
        return false;
    }
});
</script>
@endsection