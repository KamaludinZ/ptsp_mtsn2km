@extends('layouts.public')

@section('title', 'Layanan Pengaduan - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ url('/') }}">Beranda</a>
            <span class="breadcrumb-separator">/</span>
            <span>Pengaduan</span>
        </nav>

        <!-- Title -->
        <h1 class="page-title">Layanan Pengaduan</h1>
        <p class="page-subtitle">Sampaikan keluhan, saran, atau informasi penting terkait pelayanan di MTsN 2 Kota Malang</p>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">1,247</div>
                <div class="stat-label">Total Pengaduan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Ditindaklanjuti</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">7</div>
                <div class="stat-label">Hari Rata-rata</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">95%</div>
                <div class="stat-label">Puas Ditangani</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container complaint-container">
    <!-- Alternative: Direct Tabbed Interface -->
    <div class="card border-0 shadow-sm rounded-4 mb-6">
        <div class="card-header bg-white py-4 border-bottom-0">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-fill border-0" id="complaintTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dumas-alt-tab" data-bs-toggle="tab" data-bs-target="#dumas-alt" type="button" role="tab" aria-controls="dumas-alt" aria-selected="true">
                        <i class="fas fa-comment me-2"></i>Pengaduan Masyarakat
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="whistleblowing-alt-tab" data-bs-toggle="tab" data-bs-target="#whistleblowing-alt" type="button" role="tab" aria-controls="whistleblowing-alt" aria-selected="false">
                        <i class="fas fa-user-secret me-2"></i>Whistleblowing
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-4">
            <!-- Tab panes -->
            <div class="tab-content" id="complaintTabContentAlt">
                <!-- Dumas Tab -->
                <div class="tab-pane fade show active" id="dumas-alt" role="tabpanel" aria-labelledby="dumas-alt-tab">
                    <form action="{{ route('supervision.complaint.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="complaint_type" value="complaint">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="reporter_name_alt" class="form-label">Nama Pelapor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="reporter_name_alt" name="reporter_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reporter_email_alt" class="form-label">Email Pelapor <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="reporter_email_alt" name="reporter_email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reporter_phone_alt" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="reporter_phone_alt" name="reporter_phone">
                            </div>
                            <div class="col-md-6">
                                <label for="complaint_date_alt" class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="complaint_date_alt" name="complaint_date" required>
                            </div>
                            <div class="col-12">
                                <label for="complaint_title_alt" class="form-label">Judul Pengaduan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="complaint_title_alt" name="complaint_title" required>
                            </div>
                            <div class="col-12">
                                <label for="complaint_description_alt" class="form-label">Isi Pengaduan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="complaint_description_alt" name="complaint_description" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <label for="attachment_alt" class="form-label">Lampiran (jika ada)</label>
                                <input type="file" class="form-control" id="attachment_alt" name="attachment">
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pengaduan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Whistleblowing Tab -->
                <div class="tab-pane fade" id="whistleblowing-alt" role="tabpanel" aria-labelledby="whistleblowing-alt-tab">
                    <div class="alert alert-danger mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Whistleblowing</strong> - Gunakan formulir ini untuk melaporkan pelanggaran serius seperti korupsi, penipuan, suap, atau penyalahgunaan wewenang yang terjadi di dalam organisasi.
                    </div>
                    
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-4 mb-4">
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
                    
                    <form action="{{ route('supervision.complaint.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="complaint_type" value="whistleblowing">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="violation_category_alt" class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="violation_category_alt" name="violation_category" required>
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
                                <label for="incident_date_alt" class="form-label">Tanggal Kejadian</label>
                                <input type="date" class="form-control" id="incident_date_alt" name="incident_date">
                            </div>
                            <div class="col-12">
                                <label for="incident_title_alt" class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="incident_title_alt" name="complaint_title" required placeholder="Ringkasan Pelanggaran">
                            </div>
                            <div class="col-12">
                                <label for="incident_description_alt" class="form-label">Deskripsi Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="incident_description_alt" name="complaint_description" rows="5" required placeholder="Jelaskan secara detail kejadian pelanggaran..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="evidence_alt" class="form-label">Bukti Pendukung</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="evidence_alt" name="attachment" multiple>
                                    <label class="input-group-text" for="evidence_alt">Unggah File</label>
                                </div>
                                <div class="form-text">
                                    Anda dapat mengunggah beberapa file sebagai bukti pendukung (PNG, JPG, PDF, DOCX - Maks 10MB)
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="anonymous_report_alt" name="anonymous" value="1">
                                    <label class="form-check-label" for="anonymous_report_alt">
                                        Laporkan secara anonim
                                    </label>
                                </div>
                                
                                <div id="reporter_identity_section_alt">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="reporter_name_whistle_alt" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="reporter_name_whistle_alt" name="reporter_name">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reporter_position_alt" class="form-label">Jabatan/Posisi</label>
                                            <input type="text" class="form-control" id="reporter_position_alt" name="reporter_position" placeholder="Contoh: Pegawai, Kontraktor, dll">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="reporter_email_whistle_alt" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="reporter_email_whistle_alt" name="reporter_email">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reporter_phone_whistle_alt" class="form-label">Nomor Telepon</label>
                                            <input type="tel" class="form-control" id="reporter_phone_whistle_alt" name="reporter_phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-bullhorn me-2"></i> Kirim Laporan Rahasia
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- NOTE: Original form has been replaced with tabbed interface above -->
    <!-- Users can now access the complaint form through the buttons or tabbed interface -->

    <!-- Information Section -->
    <div class="row mb-6">
        <div class="col-md-6 mb-4">
            <div class="info-card">
                <h3 class="h5 fw-bold mb-3">
                    <i class="fas fa-shield-alt me-2 info-icon"></i>Perlindungan Whistleblower
                </h3>
                <p class="text-muted mb-0">
                    Kami menjamin kerahasiaan identitas pelapor dalam kasus whistleblowing dan melindungi dari segala bentuk represaliasi.
                </p>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="info-card">
                <h3 class="h5 fw-bold mb-3">
                    <i class="fas fa-clock me-2 info-icon"></i>Waktu Respons
                </h3>
                <p class="text-muted mb-0">
                    Pengaduan akan ditindaklanjuti dalam waktu maksimal 5 hari kerja sejak tanggal diterima.
                </p>
            </div>
        </div>
    </div>

    <!-- Lapor.go.id Information -->
    <div class="info-card mb-4 text-center national-complaint-card">
        <div class="row align-items-center">
            <div class="col-md-3 mb-3 mb-md-0">
                <img src="{{ asset('images/Simf60FF_SP4N-Lapor.png') }}"
                     alt="SP4N LAPOR!"
                     class="img-fluid"
                     class="national-complaint-image">
            </div>
            <div class="col-md-9 text-md-start">
                <h3 class="h5 fw-bold mb-2 national-complaint-heading">
                    <i class="fas fa-megaphone me-2 national-complaint-icon"></i>Lapor Pengaduan Nasional
                </h3>
                <p class="mb-2 national-complaint-text">
                    Selain melaporkan pengaduan kepada kami, Anda juga dapat menyampaikan aspirasi dan pengaduan pelayanan publik secara nasional melalui:
                </p>
                <a href="https://www.lapor.go.id/"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i>Kunjungi LAPOR.GO.ID
                </a>
            </div>
        </div>
    </div>

    <!-- Tracking Section -->
    <div class="tracking-card">
        <h2 class="section-title">Lacak Status Pengaduan Anda</h2>
        <p class="text-muted mb-4">Ketahui status terkini dari pengaduan yang telah Anda sampaikan</p>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form method="POST" action="{{ route('supervision.complaint.track') }}" class="space-y-4">
                    @csrf
                    <div class="form-group">
                        <label for="complaint_number" class="form-label">
                            Nomor Tiket Pengaduan
                        </label>
                        <input type="text" 
                               id="complaint_number" 
                               name="complaint_number" 
                               required
                               class="form-control"
                               placeholder="Masukkan nomor tiket">
                    </div>
                    
                    <div class="form-group">
                        <label for="reporter_email" class="form-label">
                            Email Pelapor (Opsional)
                        </label>
                        <input type="email" 
                               id="reporter_email" 
                               name="reporter_email" 
                               class="form-control"
                               placeholder="Email yang digunakan saat pengiriman">
                    </div>
                    
                    <div class="pt-2">
                        <button type="submit" 
                                class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i> Lacak Pengaduan
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="{{ route('supervision.complaint.track.form') }}" 
                       class="btn btn-outline">
                        <i class="fas fa-external-link-alt me-2"></i> Buka halaman pelacakan lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Standards Compliance Note -->
    <div class="mt-6 p-4 bg-primary-soft rounded-3 border-start border-primary border-4">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-primary info-icon-large"></i>
            </div>
            <div class="ms-4">
                <h4 class="h5 fw-bold mb-2 info-heading">
                    Kepatuhan terhadap Standar Pelayanan
                </h4>
                <p class="mb-3 info-text">
                    Layanan pengaduan ini diselaraskan dengan Peraturan Menteri PANRB Nomor 15 Tahun 2014 tentang Pedoman Pelayanan Publik.
                </p>
                <button onclick="showStandardsModal()" class="btn btn-outline-primary">
                    <i class="fas fa-list-check me-2"></i> Lihat 14 Komponen Standar Pelayanan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Standards Modal -->
<div id="standardsModal" class="modal fade standards-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">14 Komponen Standar Pelayanan</h5>
                <button type="button" class="btn-close" onclick="closeStandardsModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="space-y-3">
                    <!-- Pengaduan Layanan is the 6th component in the service -->
                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            6. Penanganan Pengaduan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Proses penanganan keluhan dan pengaduan dari masyarakat melalui sistem terpadu.
                        </p>
                    </div>

                    <!-- Other standard components -->
                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            1. Dasar Hukum
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Landasan hukum penyelenggaraan pelayanan PTSP MTsN 2 Kota Malang sesuai peraturan perundang-undangan yang berlaku.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            2. Persyaratan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Dokumen dan persyaratan yang harus dipenuhi oleh pemohon untuk mendapatkan pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            3. Sistem, Mekanisme, Prosedur
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Alur dan tata cara pelayanan dari mulai pengajuan hingga selesai.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            4. Jangka Waktu Penyelesaian
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Estimasi waktu yang dibutuhkan untuk menyelesaikan setiap pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            5. Biaya/Tarif
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Informasi biaya atau tarif pelayanan yang berlaku (jika ada).
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            7. Produk Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Hasil akhir dari pelayanan yang diberikan kepada pemohon.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            8. Sarana, Prasarana, dan Sistem
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Fasilitas dan sistem pendukung penyelenggaraan pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            9. Jumlah dan Layanan Pelaksana
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jumlah petugas yang tersedia untuk setiap layanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            10. Jaminan Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jaminan yang diberikan kepada pemohon terkait kualitas dan waktu pelayanan.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            11. Jaminan Keamanan dan Keselamatan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Jaminan keamanan data dan informasi pemohon.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            12. Evaluasi Kinerja
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Mekanisme evaluasi kinerja pelayanan secara berkala.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            13. Penetapan Standar Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Mekanisme penetapan standar pelayanan yang harus dipenuhi.
                        </p>
                    </div>

                    <div class="standards-card">
                        <h4 class="fw-bold mb-2" class="fw-bold mb-2 info-heading">
                            14. Sistem Pengelolaan Pelayanan
                        </h4>
                        <p class="mb-0" style="color: var(--bs-secondary-text);">
                            Sistem pengelolaan pelayanan yang terintegrasi dan terdokumentasi secara baik.
                        </p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <p class="mb-0 text-center" style="color: var(--bs-secondary-text);">
                        <i class="fas fa-info-circle me-2"></i>
                        Untuk informasi lebih lengkap, silakan hubungi PTSP MTsN 2 Kota Malang.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Function to show standards modal
    function showStandardsModal() {
        const modal = document.getElementById('standardsModal');
        modal.classList.add('show', 'd-block');
        modal.style.display = 'block';
        modal.setAttribute('aria-hidden', 'false');
        // Add backdrop
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');
    }

    // Function to close standards modal
    function closeStandardsModal() {
        const modal = document.getElementById('standardsModal');
        modal.classList.remove('show', 'd-block');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
    }
    
    // Handle anonymous checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const anonymousCheckbox = document.getElementById('anonymous_report_alt');
        const identityFields = document.getElementById('reporter_identity_section_alt');
        
        if (anonymousCheckbox && identityFields) {
            // Initially hide identity fields if anonymous is checked
            if (anonymousCheckbox.checked) {
                identityFields.style.display = 'none';
            }
            
            // Toggle identity fields visibility when checkbox changes
            anonymousCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    identityFields.style.display = 'none';
                } else {
                    identityFields.style.display = 'block';
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var triggerTabList = [].slice.call(document.querySelectorAll('#complaintTab button'))
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    });
</script>
@endpush
