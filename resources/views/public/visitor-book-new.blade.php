@extends('layouts.public')

@section('title', 'Buku Tamu - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@section('content')

<!-- Page Header -->
<div class="py-5 page-header-gradient">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-4 breadcrumb-nav">
            <a href="{{ url('/') }}" class="text-white-50 breadcrumb-link">Beranda</a>
            <span class="mx-2 text-white breadcrumb-separator">/</span>
            <span class="text-white breadcrumb-current">Buku Tamu</span>
        </nav>

        <!-- Title -->
        <h1 class="text-white page-title-main">Buku Tamu</h1>
        <p class="text-white page-subtitle-main">Pencatatan kunjungan tamu fisik ke MTsN 2 Kota Malang</p>

        <!-- Statistics -->
        <div class="row mt-5 g-4">
            <div class="col-md-3">
                <div class="bg-white bg-opacity-15 rounded p-3 text-center border border-white border-opacity-20 stat-card">
                    <div class="text-white stat-number">{{ $visitors->total() }}</div>
                    <div class="text-white text-opacity-85 stat-label">Total Tamu Hari Ini</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-15 rounded p-3 text-center border border-white border-opacity-20 stat-card">
                    <div class="text-white stat-number">{{ $visitors->whereNull('checkout_at')->count() }}</div>
                    <div class="text-white text-opacity-85 stat-label">Sedang Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-15 rounded p-3 text-center border border-white border-opacity-20 stat-card">
                    <div class="text-white stat-number">{{ \Carbon\Carbon::parse($date)->format('d') }}</div>
                    <div class="text-white text-opacity-85 stat-label">Tanggal</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white bg-opacity-15 rounded p-3 text-center border border-white border-opacity-20 stat-card">
                    <div class="text-white stat-number">{{ \Carbon\Carbon::parse($date)->format('M Y') }}</div>
                    <div class="text-white text-opacity-85 stat-label">Bulan & Tahun</div>
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
                <button class="nav-link active py-3 px-4" id="visitor-tab" data-bs-toggle="tab" data-bs-target="#visitor-form" type="button" role="tab">Formulir Tamu Kunjungan</button>
                <button class="nav-link py-3 px-4" id="applicant-tab" data-bs-toggle="tab" data-bs-target="#applicant-form" type="button" role="tab">Formulir Tamu Pemohon Layanan Offline</button>
            </div>
        </div>

        <div class="card-body">
            <div class="tab-content" id="formTabsContent">
                <!-- Visitor Form -->
                <div class="tab-pane fade show active" id="visitor-form" role="tabpanel">
                    <form action="#" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">No. Telepon/HP *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Instansi/Perusahaan</label>
                                <input type="text" class="form-control" name="institution">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tujuan Kunjungan *</label>
                                <input type="text" class="form-control" name="purpose" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">Keperluan Lainnya</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="obscureNameVisitor" name="obscure_name">
                                    <label class="form-check-label" for="obscureNameVisitor">Samarkan Nama di Daftar Tamu</label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Daftar Tamu</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Service Applicant Form -->
                <div class="tab-pane fade" id="applicant-form" role="tabpanel">
                    <form action="#" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">No. Telepon/HP *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Instansi/Perusahaan</label>
                                <input type="text" class="form-control" name="institution">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Pemohon</label>
                                <select class="form-control" name="applicant_type">
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
                                <label class="form-label fw-bold">Layanan yang Dituju *</label>
                                <input type="text" class="form-control" name="target_service" required placeholder="Contoh: Pengambilan Ijazah, Surat Keterangan, dll">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold">Catatan Tambahan</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Jelaskan secara singkat keperluan Anda"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="obscureNameApplicant" name="obscure_name">
                                    <label class="form-check-label" for="obscureNameApplicant">Samarkan Nama di Daftar Tamu</label>
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

    <!-- Visitor List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Tamu {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
            </h2>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-nowrap">No</th>
                            <th scope="col">Nama Tamu</th>
                            <th scope="col">Instansi</th>
                            <th scope="col">Tujuan</th>
                            <th scope="col" class="text-nowrap">Check In</th>
                            <th scope="col" class="text-nowrap">Check Out</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $index => $visitor)
                            <tr>
                                <td class="text-nowrap">{{ ($visitors->currentPage() - 1) * $visitors->perPage() + $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold">
                                        @if ($visitor->is_obscured)
                                            <script>document.write(obscureName("{{ $visitor->name }}"))</script>
                                        @else
                                            {{ $visitor->name }}
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $visitor->phone }}</small>
                                </td>
                                <td>{{ $visitor->institution ?: '-' }}</td>
                                <td>{{ Str::limit($visitor->purpose, 30) }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($visitor->created_at)->format('H:i') }}</td>
                                <td class="text-nowrap">{{ $visitor->checkout_at ? \Carbon\Carbon::parse($visitor->checkout_at)->format('H:i') : '-' }}</td>
                                <td>
                                    @if ($visitor->checkout_at)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-check-circle me-1"></i>Selesai
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle me-1"></i>Aktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
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
                            Menampilkan <strong>{{ $visitors->firstItem() }}</strong>
                            hingga <strong>{{ $visitors->lastItem() }}</strong>
                            dari <strong>{{ $visitors->total() }}</strong> hasil
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
    /* Page header gradient */
    .page-header-gradient {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
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
</style>
@endsection