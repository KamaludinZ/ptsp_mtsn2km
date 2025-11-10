@extends('layouts.public')

@section('title', 'Buku Tamu - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@section('content')

<!-- Page Header -->
<div class="py-5 page-header-gradient">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-4 breadcrumb-nav">
            <a href="{{ url('/') }}" class="text-white breadcrumb-link">Beranda</a>
            <span class="mx-2 text-white breadcrumb-separator">/</span>
            <span class="text-white breadcrumb-current">Buku Tamu</span>
        </nav>

        <!-- Title -->
        <h1 class="text-white page-title-main">Buku Tamu</h1>
        <p class="text-white page-subtitle-main">Pencatatan kunjungan tamu fisik ke MTsN 2 Kota Malang</p>

        <!-- Statistics -->
        <div class="row mt-5 g-4">
            <div class="col-md-3">
                <div class="bg-white bg-opacity-25 rounded p-3 text-center border border-white border-opacity-30 stat-card">
                    <div class="text-white stat-number">{{ $visitors->total() }}</div>
                    <div class="text-white stat-label">Total Tamu Hari Ini</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-25 rounded p-3 text-center border border-white border-opacity-30 stat-card">
                    <div class="text-white stat-number">{{ $visitors->whereNull('checkout_at')->count() }}</div>
                    <div class="text-white stat-label">Sedang Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-25 rounded p-3 text-center border border-white border-opacity-30 stat-card">
                    <div class="text-white stat-number">{{ \Carbon\Carbon::parse($date)->format('d') }}</div>
                    <div class="text-white stat-label">Tanggal</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-25 rounded p-3 text-center border border-white border-opacity-30 stat-card">
                    <div class="text-white stat-number">{{ \Carbon\Carbon::parse($date)->format('M Y') }}</div>
                    <div class="text-white stat-label">Bulan & Tahun</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4 pb-5">
    <!-- Form Tabs -->
    <div class="card shadow-sm mb-5">
        <div class="card-header p-0">
            <div class="nav nav-tabs" id="formTabs" role="tablist">
                <button class="nav-link active py-3 px-4" id="visitor-tab" data-bs-toggle="tab" data-bs-target="#visitor-form" type="button" role="tab" >Formulir Tamu Kunjungan</button>
                <button class="nav-link py-3 px-4" id="applicant-tab" data-bs-toggle="tab" data-bs-target="#applicant-form" type="button" role="tab" >Formulir Tamu Pemohon Layanan Offline</button>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="formTabsContent">
                <!-- Visitor Form -->
                <div class="tab-pane fade show active" id="visitor-form" role="tabpanel">
                    <div class="p-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(34, 197, 94, 0.05) 100%); border-radius: 0.375rem;">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Nama Lengkap *</label>
                                    <input type="text" class="form-control responsive-input" name="name" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">No. Telepon/HP *</label>
                                    <input type="tel" class="form-control responsive-input" name="phone" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Instansi/Perusahaan</label>
                                    <input type="text" class="form-control responsive-input" name="institution">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Tujuan Kunjungan *</label>
                                    <input type="text" class="form-control responsive-input" name="purpose" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold info-heading">Keperluan Lainnya</label>
                                    <textarea class="form-control responsive-input" name="notes" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="obscureNameVisitor" name="obscure_name">
                                        <label class="form-check-label info-heading" for="obscureNameVisitor">Samarkan Nama di Daftar Tamu</label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Daftar Tamu</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Service Applicant Form -->
                <div class="tab-pane fade" id="applicant-form" role="tabpanel">
                    <div class="p-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(34, 197, 94, 0.05) 100%); border-radius: 0.375rem;">
                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Nama Lengkap *</label>
                                    <input type="text" class="form-control responsive-input" name="name" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">No. Telepon/HP *</label>
                                    <input type="tel" class="form-control responsive-input" name="phone" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Instansi/Perusahaan</label>
                                    <input type="text" class="form-control responsive-input" name="institution">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold info-heading">Status Pemohon</label>
                                    <select class="form-control responsive-input" name="applicant_type">
                                        <option value="">Pilih Status</option>
                                        <option value="siswa">Siswa</option>
                                        <option value="wali_murid">Wali Murid</option>
                                        <option value="alumni">Alumni</option>
                                        <option value="pegawai">Pegawai</option>
                                        <option value="umum">Masyarakat Umum</option>
                                        <option value="instansi">Instansi Pemerintah</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold info-heading">Layanan yang Dituju *</label>
                                    <input type="text" class="form-control responsive-input" name="target_service" required placeholder="Contoh: Pengambilan Ijazah, Surat Keterangan, dll">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold info-heading">Catatan Tambahan</label>
                                    <textarea class="form-control responsive-input" name="notes" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="obscureNameApplicant" name="obscure_name">
                                        <label class="form-check-label info-heading" for="obscureNameApplicant">Samarkan Nama di Daftar Tamu</label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Daftar Pemohon Layanan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor List -->
    <div class="card shadow-sm mt-5">
        <div class="card-header" style="background: linear-gradient(135deg, rgba(20, 83, 45, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);">
            <h2 class="mb-0 info-heading">
                <i class="fas fa-list me-2"></i>Daftar Tamu 
                <span class="text-primary">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
            </h2>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-nowrap info-heading">No</th>
                            <th scope="col" class="info-heading">Nama Tamu</th>
                            <th scope="col" class="info-heading">Instansi</th>
                            <th scope="col" class="info-heading">Tujuan</th>
                            <th scope="col" class="text-nowrap info-heading">Check In</th>
                            <th scope="col" class="text-nowrap info-heading">Check Out</th>
                            <th scope="col" class="info-heading">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $index => $visitor)
                            <tr>
                                <td class="text-nowrap info-text">{{ ($visitors->currentPage() - 1) * $visitors->perPage() + $index + 1 }}</td>
                                <td class="info-text">
                                    <div class="fw-bold">
                                        @if ($visitor->is_obscured)
                                            <script>document.write(obscureName("{{ $visitor->name }}"))</script>
                                        @else
                                            {{ $visitor->name }}
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $visitor->phone }}</small>
                                </td>
                                <td class="info-text">{{ $visitor->institution ?: '-' }}</td>
                                <td class="info-text">{{ Str::limit($visitor->purpose, 30) }}</td>
                                <td class="text-nowrap info-text">{{ \Carbon\Carbon::parse($visitor->created_at)->format('H:i') }}</td>
                                <td class="text-nowrap info-text">{{ $visitor->checkout_at ? \Carbon\Carbon::parse($visitor->checkout_at)->format('H:i') : '-' }}</td>
                                <td>
                                    @if ($visitor->checkout_at)
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>Selesai
                                        </span>
                                    @else
                                        <span class="badge bg-success text-white px-3 py-2">
                                            <i class="fas fa-circle me-1"></i>Aktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 info-text">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Belum Ada Tamu</h4>
                                    <p class="text-muted">
                                        Belum ada data tamu untuk tanggal {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($visitors->hasPages())
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="info-text">
                                Menampilkan <strong>{{ $visitors->firstItem() }}</strong>
                                hingga <strong>{{ $visitors->lastItem() }}</strong>
                                dari <strong>{{ $visitors->total() }}</strong> hasil
                            </span>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-md-end">
                                {{ $visitors->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Function to obscure a name (corrected)
    function obscureName(name) {
        if (!name) return '';
        const parts = name.split(' ');
        return parts.map(part => {
            if (part.length <= 2) return part;
            return part.charAt(0) + '*'.repeat(part.length - 2) + part.charAt(part.length - 1);
        }).join(' ');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the active tab
        const activeTab = localStorage.getItem('activeTab') || '#visitor-form';
        if(activeTab === '#applicant-form') {
            const applicantTab = document.getElementById('applicant-tab');
            if(applicantTab) {
                applicantTab.click();
            }
        }
        
        // Save the active tab in localStorage
        const tabs = document.querySelectorAll('.nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                localStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>

<style>
    /* Responsive text color for dark mode */
    .responsive-text {
        color: #1f2937;
    }

    [data-theme="dark"] .responsive-text {
        color: var(--bs-text) !important;
    }

    /* Info text and heading colors */
    .info-text {
        color: var(--bs-secondary-text);
    }

    .info-heading {
        color: var(--bs-text);
        font-weight: bold;
    }

    [data-theme="dark"] .info-text {
        color: var(--bs-secondary-text) !important;
    }

    [data-theme="dark"] .info-heading {
        color: var(--bs-text) !important;
    }
    
    /* Ensure consistent button styling */
    .btn-consistent {
        border-width: 1px;
        border-style: solid;
    }
    
    /* Dark mode support for cards */
    [data-theme="dark"] .card {
        background: var(--bs-surface) !important;
        border-color: var(--bs-border) !important;
    }
    
    [data-theme="dark"] .card-header {
        background: var(--bs-surface) !important;
        border-color: var(--bs-border) !important;
    }
    
    /* Dark mode support for tables */
    [data-theme="dark"] .table {
        color: var(--bs-text) !important;
    }
    
    [data-theme="dark"] .table th {
        color: var(--bs-text) !important;
        background: var(--bs-surface) !important;
    }
    
    [data-theme="dark"] .table td {
        color: var(--bs-text) !important;
    }
    
    /* Dark mode support for form elements */
    [data-theme="dark"] .form-control {
        background: var(--bs-surface) !important;
        border-color: var(--bs-border) !important;
        color: var(--bs-text) !important;
    }
    
    [data-theme="dark"] .form-select {
        background: var(--bs-surface) !important;
        border-color: var(--bs-border) !important;
        color: var(--bs-text) !important;
    }
    
    /* Enhanced tab styling */
    .nav-tabs {
        border-bottom: 2px solid rgba(20, 83, 45, 0.1) !important;
    }
    
    .nav-tabs .nav-link {
        color: var(--bs-text) !important;
        background: transparent !important;
        border: 2px solid transparent !important;
        border-bottom: none !important;
        border-radius: 0.5rem 0.5rem 0 0 !important;
        padding: 1rem 1.5rem !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--bs-primary) !important;
        background: rgba(20, 83, 45, 0.05) !important;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        background: var(--bs-white) !important;
        border-color: var(--bs-primary) !important;
        border-bottom: 2px solid var(--bs-white) !important;
        position: relative;
    }
    
    [data-theme="dark"] .nav-tabs .nav-link {
        color: var(--bs-gray-300) !important;
        background: transparent !important;
        border-color: transparent !important;
    }
    
    [data-theme="dark"] .nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        background: var(--bs-gray-800) !important;
        border-color: var(--bs-primary) !important;
        border-bottom: 2px solid var(--bs-gray-800) !important;
    }
    
    [data-theme="dark"] .nav-tabs .nav-link:hover {
        background: rgba(20, 83, 45, 0.1) !important;
    }
    
    /* Page header gradient */
    .page-header-gradient {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
    }
    
    /* Responsive input fields */
    .responsive-input {
        color: #111827;
    }
    
    [data-theme="dark"] .responsive-input {
        color: var(--bs-text) !important;
        background: var(--bs-surface) !important;
    }
    
    /* Header elements */
    .breadcrumb-nav {
        font-size: 14px;
    }
    
    .breadcrumb-link {
        opacity: 0.8;
        text-decoration: none;
    }
    
    .breadcrumb-separator {
        opacity: 0.6;
    }
    
    .breadcrumb-current {
        opacity: 1;
    }
    
    .page-title-main {
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .page-subtitle-main {
        opacity: 0.9;
        font-size: 1.1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .stat-label {
        font-weight: 500;
        opacity: 0.9;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    /* Dark mode support for header elements */
    [data-theme="dark"] .breadcrumb-link,
    [data-theme="dark"] .breadcrumb-separator,
    [data-theme="dark"] .breadcrumb-current,
    [data-theme="dark"] .page-title-main,
    [data-theme="dark"] .page-subtitle-main {
        color: var(--bs-white) !important;
    }
    
    [data-theme="dark"] .stat-number,
    [data-theme="dark"] .stat-label {
        color: var(--bs-white) !important;
    }
    
    /* Enhanced card header styling */
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    
    .card.shadow-sm.mt-5 {
        border-top-left-radius: 0.5rem !important;
        border-top-right-radius: 0.5rem !important;
    }
</style>
@endsection