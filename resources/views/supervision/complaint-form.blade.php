@extends('layouts.public')

@section('title', 'Formulir Pengaduan')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="display-4 fw-bold mb-3">
            Formulir <span style="color: var(--bs-primary);">Pengaduan</span>
        </h1>
        <p class="lead text-muted">
            Sampaikan keluhan, saran, atau masukan Anda untuk perbaikan layanan kami.
        </p>
    </div>

    <!-- Tab Navigation -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-4 border-bottom-0">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-fill border-0" id="complaintTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active bg-gradient" id="dumas-tab" data-bs-toggle="tab" data-bs-target="#dumas" type="button" role="tab" aria-controls="dumas" aria-selected="true" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: 1px solid #2980b9; border-radius: 8px 8px 0 0; transition: all 0.3s ease;">
                                <i class="fas fa-comment me-2"></i>Pengaduan Masyarakat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link bg-gradient" id="whistleblowing-tab" data-bs-toggle="tab" data-bs-target="#whistleblowing" type="button" role="tab" aria-controls="whistleblowing" aria-selected="false" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: 1px solid #c0392b; border-radius: 8px 8px 0 0; transition: all 0.3s ease;">
                                <i class="fas fa-user-secret me-2"></i>Whistleblowing
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4 bg-light" style="background-color: var(--bs-gray-100);">
                    <!-- Tab panes -->
                    <div class="tab-content" id="complaintTabContent">
                        <!-- Dumas Tab -->
                        <div class="tab-pane fade show active p-4 rounded" id="dumas" role="tabpanel" aria-labelledby="dumas-tab" style="background-color: var(--bs-white, #ffffff); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="alert alert-info mb-4" role="alert" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border: 1px solid #90caf9; color: #000000;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Pengaduan Masyarakat</strong> - Gunakan formulir ini untuk melaporkan keluhan terkait pelayanan publik, seperti pelayanan lambat, prosedur berbelit, petugas tidak ramah, dan sebagainya.
                            </div>
                            
                            <form action="{{ route('supervision.complaint.submit.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="complaint_type" value="complaint">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="reporter_name" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Nama Pelapor <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="reporter_name" name="reporter_name" required style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reporter_email" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Email Pelapor <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="reporter_email" name="reporter_email" required style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reporter_phone" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Nomor Telepon</label>
                                        <input type="tel" class="form-control" id="reporter_phone" name="reporter_phone" style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="complaint_date" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Tanggal Kejadian <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="complaint_date" name="complaint_date" required style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-12">
                                        <label for="complaint_title" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Judul Pengaduan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="complaint_title" name="complaint_title" required style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-12">
                                        <label for="complaint_description" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Isi Pengaduan <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="complaint_description" name="complaint_description" rows="5" required style="color: var(--bs-body-color, #212529);"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="attachment" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Lampiran (jika ada)</label>
                                        <input type="file" class="form-control" id="attachment" name="attachment" style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg" id="submit-button-dumas" style="background: linear-gradient(135deg, #3498db, #2980b9); border: none; color: white;">
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            <i class="fas fa-paper-plane me-2"></i> Kirim Pengaduan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Whistleblowing Tab -->
                        <div class="tab-pane fade p-4 rounded" id="whistleblowing" role="tabpanel" aria-labelledby="whistleblowing-tab" style="background-color: var(--bs-white, #ffffff); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="alert alert-danger mb-4" role="alert" style="background: linear-gradient(135deg, #ffebee, #ffcdd2); border: 1px solid #ef9a9a; color: #000000;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Whistleblowing</strong> - Gunakan formulir ini untuk melaporkan pelanggaran serius seperti korupsi, penipuan, suap, atau penyalahgunaan wewenang yang terjadi di dalam organisasi.
                            </div>
                            
                            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-4 mb-4" style="background: linear-gradient(135deg, #fff5f5, #ffebee); color: var(--bs-emphasis-color, #000000);">
                                <div class="flex flex-col md:flex-row items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-red-100 rounded-full p-3">
                                            <i class="fas fa-shield-alt text-red-600 text-2xl"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-gray-900 mb-1">
                                            <i class="fas fa-lock me-2"></i> Kerahasiaan Terjamin
                                        </h5>
                                        <p class="text-gray-700">
                                            Kami menjamin kerahasiaan identitas pelapor dan melindungi dari segala bentuk represaliasi. Laporan Anda akan ditangani dengan profesional dan rahasia.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="{{ route('supervision.complaint.submit.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="complaint_type" value="whistleblowing">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="violation_category" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Kategori Pelanggaran <span class="text-danger">*</span></label>
                                        <select class="form-select" id="violation_category" name="violation_category" required style="color: var(--bs-body-color, #212529);">
                                            <option value="">Pilih Kategori</option>
                                            <option value="corruption">Korupsi</option>
                                            <option value="gratification">Gratifikasi</option>
                                            <option value="nepotism">Nepotisme/Kolusi</option>
                                            <option value="misconduct">Pelanggaran Etika</option>
                                            <option value="misuse">Penyalahgunaan Wewenang</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="incident_date" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Tanggal Kejadian</label>
                                        <input type="date" class="form-control" id="incident_date" name="incident_date" style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-12">
                                        <label for="incident_title" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Judul Laporan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="incident_title" name="complaint_title" required placeholder="Ringkasan Pelanggaran" style="color: var(--bs-body-color, #212529);">
                                    </div>
                                    <div class="col-12">
                                        <label for="incident_description" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Deskripsi Kejadian <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="incident_description" name="complaint_description" rows="5" required placeholder="Jelaskan secara detail kejadian pelanggaran..." style="color: var(--bs-body-color, #212529);"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="evidence" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Bukti Pendukung</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="evidence" name="attachment" multiple style="color: var(--bs-body-color, #212529);">
                                            <label class="input-group-text" for="evidence">Unggah File</label>
                                        </div>
                                        <div class="form-text">
                                            Anda dapat mengunggah beberapa file sebagai bukti pendukung (PNG, JPG, PDF, DOCX - Maks 10MB)
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="anonymous_report" name="anonymous" value="1">
                                            <label class="form-check-label" for="anonymous_report" style="color: var(--bs-emphasis-color, #000000);">
                                                Laporkan secara anonim
                                            </label>
                                        </div>
                                        
                                        <div id="reporter_identity_section">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="reporter_name_whistle" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Nama Lengkap</label>
                                                    <input type="text" class="form-control" id="reporter_name_whistle" name="reporter_name" style="color: var(--bs-body-color, #212529);">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="reporter_position" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Jabatan/Posisi</label>
                                                    <input type="text" class="form-control" id="reporter_position" name="reporter_position" placeholder="Contoh: Pegawai, Kontraktor, dll" style="color: var(--bs-body-color, #212529);">
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <label for="reporter_email_whistle" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Email</label>
                                                    <input type="email" class="form-control" id="reporter_email_whistle" name="reporter_email" style="color: var(--bs-body-color, #212529);">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="reporter_phone_whistle" class="form-label" style="color: var(--bs-emphasis-color, #000000);">Nomor Telepon</label>
                                                    <input type="tel" class="form-control" id="reporter_phone_whistle" name="reporter_phone" style="color: var(--bs-body-color, #212529);">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-danger btn-lg" id="submit-button-whistle" style="background: linear-gradient(135deg, #e74c3c, #c0392b); border: none; color: white;">
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            <i class="fas fa-bullhorn me-2"></i> Kirim Laporan Rahasia
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle anonymous checkbox for whistleblowing form
        const anonymousCheckbox = document.getElementById('anonymous_report');
        const reporterIdentitySection = document.getElementById('reporter_identity_section');
        
        if (anonymousCheckbox && reporterIdentitySection) {
            // Initially show the identity section unless checkbox is checked
            if (anonymousCheckbox.checked) {
                reporterIdentitySection.style.display = 'none';
            }
            
            anonymousCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    reporterIdentitySection.style.display = 'none';
                } else {
                    reporterIdentitySection.style.display = 'block';
                }
            });
        }
        
        // Handle submit buttons
        const formDumas = document.querySelector('#dumas form');
        const submitButtonDumas = document.getElementById('submit-button-dumas');
        
        const formWhistle = document.querySelector('#whistleblowing form');
        const submitButtonWhistle = document.getElementById('submit-button-whistle');
        
        if (formDumas && submitButtonDumas) {
            formDumas.addEventListener('submit', function () {
                submitButtonDumas.disabled = true;
                submitButtonDumas.querySelector('.spinner-border').classList.remove('d-none');
            });
        }
        
        if (formWhistle && submitButtonWhistle) {
            formWhistle.addEventListener('submit', function () {
                submitButtonWhistle.disabled = true;
                submitButtonWhistle.querySelector('.spinner-border').classList.remove('d-none');
            });
        }
        
        // Add hover effects to tabs
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            tab.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush
