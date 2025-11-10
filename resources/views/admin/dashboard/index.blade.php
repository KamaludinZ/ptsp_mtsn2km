@extends('layouts.admin')

@section('title', 'Dashboard - Super Admin')

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                <i class="bi bi-speedometer2 me-2" style="font-size: 1.5rem;"></i>Dashboard Super Admin
            </h1>
            <p class="text-muted mb-0">Overview sistem keseluruhan PTSP MTsN 2 Kota Malang</p>
        </div>
        <div>
            <span class="badge bg-success px-3 py-2" style="font-size: 0.95rem;">
                <i class="bi bi-shield-check me-2" style="font-size: 1.1rem;"></i>System Online
            </span>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%) !important;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Total Users</h6>
                            <h3 class="mb-0 fw-bold" style="color: #ffffff !important;">{{ number_format($totalUsers) }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Jumlah pengguna terdaftar</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="bi bi-people fs-2" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Services -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Total Services</h6>
                            <h3 class="mb-0 fw-bold" style="color: #ffffff !important;">{{ number_format($totalServices) }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Jumlah layanan tersedia</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="bi bi-card-checklist fs-2" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Total Tickets</h6>
                            <h3 class="mb-0 fw-bold" style="color: #ffffff !important;">{{ number_format($totalTickets) }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Jumlah tiket layanan</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="bi bi-ticket-perforated fs-2" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Visitors -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 stats-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase mb-1" style="color: rgba(255, 255, 255, 0.75) !important;">Pengunjung Bulan Ini</h6>
                            <h3 class="mb-0 fw-bold" style="color: #ffffff !important;">{{ number_format($visitorsThisMonth) }}</h3>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.75) !important;">Statistik pengunjung aktif</p>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(255, 255, 255, 0.25) !important;">
                            <i class="bi bi-person-walking fs-2" style="color: #ffffff !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Status Cards Section -->

    <!-- Tiket Status Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--bs-text);">
                <i class="bi bi-ticket-perforated-fill me-2 text-primary"></i>Status Tiket Layanan
            </h5>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Tiket Baru Hari Ini</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ number_format($ticketsToday) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-check text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Belum Diproses</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($ticketsIncoming) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sedang Diproses</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($ticketsProcessing) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-gear text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Menunggu Approval</p>
                            <h3 class="mb-0 fw-bold" style="color: #6366f1;">{{ number_format($ticketsPendingApproval) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(99, 102, 241, 0.1);">
                            <i class="bi bi-hourglass-split fs-4" style="color: #6366f1;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Tiket Selesai</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($ticketsCompleted) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Approval Ditolak</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ number_format($ticketsRejected) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-x-circle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengaduan Status Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--bs-text);">
                <i class="bi bi-megaphone-fill me-2 text-danger"></i>Status Pengaduan Masyarakat
            </h5>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Masuk Hari Ini</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ number_format($complaintsToday) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Belum Diproses</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($complaintsUnprocessed) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-clock text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sedang Diproses</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($complaintsProcessing) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-arrow-repeat text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Selesai</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($complaintsCompleted) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Whistleblowing Status Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--bs-text);">
                <i class="bi bi-shield-exclamation me-2" style="color: #dc2626;"></i>Status Whistleblowing
            </h5>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Masuk Hari Ini</p>
                            <h3 class="mb-0 fw-bold" style="color: #dc2626;">{{ number_format($whistleblowingToday) }}</h3>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(220, 38, 38, 0.1);">
                            <i class="bi bi-flag-fill fs-4" style="color: #dc2626;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Belum Diproses</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($whistleblowingUnprocessed) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-eye-slash text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sedang Diproses</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($whistleblowingProcessing) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-search text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Selesai</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($whistleblowingCompleted) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Response Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--bs-text);">
                <i class="bi bi-clipboard-data-fill me-2 text-success"></i>Status Survei Kepuasan
            </h5>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Jawaban Hari Ini</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ number_format($surveyResponsesToday) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar-day text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Jawaban Bulan Ini</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($surveyResponsesThisMonth) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar2-month text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Jawaban Triwulan Ini</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($surveyResponsesThisQuarter) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-calendar3 text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Nilai Bulan Ini</p>
                            <h3 class="mb-0 fw-bold" style="color: #f59e0b;">{{ number_format($surveyScoreThisMonth, 2) }}/5</h3>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="bi bi-star-fill fs-4" style="color: #f59e0b;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Nilai Triwulan Ini</p>
                            <h3 class="mb-0 fw-bold" style="color: #f59e0b;">{{ number_format($surveyScoreThisQuarter, 2) }}/5</h3>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="bi bi-stars fs-4" style="color: #f59e0b;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Nilai Tahun Ini</p>
                            <h3 class="mb-0 fw-bold" style="color: #f59e0b;">{{ number_format($surveyScoreThisYear, 2) }}/5</h3>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="bi bi-trophy-fill fs-4" style="color: #f59e0b;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor Status Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--bs-text);">
                <i class="bi bi-people-fill me-2 text-info"></i>Status Pengunjung
            </h5>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pengunjung Hari Ini</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ number_format($visitorsToday) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-person-check text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pengunjung Aktif</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ number_format($visitorsActive) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-person-walking text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pengunjung Bulan Ini</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($visitorsThisMonth) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-people text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-3" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Kinerja Layanan
                        </h5>
                        <select id="servicePerformanceFilter" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem;">
                            <option value="day">Harian</option>
                            <option value="week">Mingguan</option>
                            <option value="month">Bulanan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="servicePerformanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-3" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                            <i class="bi bi-speedometer2 text-warning me-2"></i>Tindak Lanjut Pengaduan
                        </h5>
                        <select id="complaintPerformanceFilter" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem;">
                            <option value="day">Harian</option>
                            <option value="week">Mingguan</option>
                            <option value="month">Bulanan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="complaintPerformanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-3" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                            <i class="bi bi-shield-exclamation text-danger me-2"></i>Tindak Lanjut Whistleblowing
                        </h5>
                        <select id="whistleblowingPerformanceFilter" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem;">
                            <option value="day">Harian</option>
                            <option value="week">Mingguan</option>
                            <option value="month">Bulanan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="whistleblowingPerformanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Analytics Section -->
    <div class="row g-4 mb-4 mt-0">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-3" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                            <i class="bi bi-star-fill text-success me-2"></i>Analisis Hasil Survei
                        </h5>
                        <select id="surveyAnalyticsFilter" class="form-select form-select-sm" style="width: auto; font-size: 0.875rem;">
                            <option value="day">Harian</option>
                            <option value="week">Mingguan</option>
                            <option value="month" selected>Bulanan</option>
                            <option value="year">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-1 opacity-75" style="font-size: 0.875rem;">Total Responden</p>
                                            <h2 class="mb-0 fw-bold" id="totalRespondents">0</h2>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                            <i class="bi bi-people-fill fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-1 opacity-75" style="font-size: 0.875rem;">Indeks Kepuasan</p>
                                            <h2 class="mb-0 fw-bold"><span id="satisfactionIndex">0</span>%</h2>
                                            <p class="mb-0 opacity-75" style="font-size: 0.75rem;">Rating: <span id="avgRating">0</span>/4</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                            <i class="bi bi-emoji-smile-fill fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div class="row g-3">
                        <!-- Response Trend -->
                        <div class="col-lg-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">Trend Responden</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="responseTrendChart" height="60"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Age Distribution -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">Kategori Usia</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="ageDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Occupation Distribution -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">Pekerjaan</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="occupationDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Education Distribution -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">Pendidikan</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="educationDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Service Distribution -->
                        <div class="col-lg-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 fw-bold">Jenis Layanan yang Disurvei</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="serviceDistributionChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security and Recent Activity Section -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-3" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                        <i class="bi bi-shield-lock-fill text-danger me-2"></i>Informasi Keamanan
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center list-item-hover" style="border-color: var(--bs-border); background-color: transparent;">
                            <span style="color: var(--bs-text);">
                                <i class="bi bi-app-indicator me-2 text-primary"></i>Versi Aplikasi
                            </span>
                            <span class="badge bg-primary">{{ $appVersion }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center list-item-hover" style="border-color: var(--bs-border); background-color: transparent;">
                            <span style="color: var(--bs-text);">
                                <i class="bi bi-shield-fill-check me-2 text-success"></i>Firewall
                            </span>
                            <span class="badge bg-success">{{ $firewallStatus }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center list-item-hover" style="border-color: var(--bs-border); background-color: transparent;">
                            <span style="color: var(--bs-text);">
                                <i class="bi bi-ban me-2 text-danger"></i>IP Diblokir
                            </span>
                            <span class="badge bg-danger">{{ $blockedIps }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center list-item-hover" style="border-color: var(--bs-border); background-color: transparent;">
                            <span style="color: var(--bs-text);">
                                <i class="bi bi-tools me-2 text-secondary"></i>Mode Perbaikan
                            </span>
                            <span class="badge bg-secondary">{{ $maintenanceMode }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm hover-lift" style="background-color: var(--bs-bg); border: 1px solid var(--bs-border) !important;">
                <div class="card-header border-0 pt-4 pb-0" style="background-color: var(--bs-surface); border-bottom: 1px solid var(--bs-border) !important;">
                    <h5 class="mb-0 fw-bold" style="color: var(--bs-text);">
                        <i class="bi bi-activity text-info me-2"></i>Aktivitas Terbaru
                    </h5>
                    <ul class="nav nav-tabs card-header-tabs mt-3" id="activityTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="logins-tab" data-bs-toggle="tab" data-bs-target="#logins" type="button" role="tab" aria-controls="logins" aria-selected="true">
                                <i class="bi bi-person-circle me-1"></i>User Logins
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab" aria-controls="tickets" aria-selected="false">
                                <i class="bi bi-ticket-perforated me-1"></i>Tiket Layanan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="visitors-tab" data-bs-toggle="tab" data-bs-target="#visitors" type="button" role="tab" aria-controls="visitors" aria-selected="false">
                                <i class="bi bi-journal-check me-1"></i>Buku Tamu
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="complaints-tab" data-bs-toggle="tab" data-bs-target="#complaints" type="button" role="tab" aria-controls="complaints" aria-selected="false">
                                <i class="bi bi-megaphone me-1"></i>Pengaduan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="surveys-tab" data-bs-toggle="tab" data-bs-target="#surveys" type="button" role="tab" aria-controls="surveys" aria-selected="false">
                                <i class="bi bi-clipboard-data me-1"></i>Responden Survei
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="activityTabContent">
                        <div class="tab-pane fade show active" id="logins" role="tabpanel" aria-labelledby="logins-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: var(--bs-text);"><i class="bi bi-person-badge me-1"></i>Nama</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-envelope me-1"></i>Email</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-clock-history me-1"></i>Waktu Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentLogins as $user)
                                            <tr>
                                                <td style="color: var(--bs-text);">{{ $user->name }}</td>
                                                <td style="color: var(--bs-text);">{{ $user->email }}</td>
                                                <td style="color: var(--bs-text);">{{ $user->updated_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center" style="color: var(--bs-text);">Tidak ada aktivitas login terbaru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tickets" role="tabpanel" aria-labelledby="tickets-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: var(--bs-text);"><i class="bi bi-hash me-1"></i>ID Tiket</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-briefcase me-1"></i>Layanan</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-person me-1"></i>Pengguna</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-flag me-1"></i>Status</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-clock me-1"></i>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTickets as $ticket)
                                            <tr>
                                                <td style="color: var(--bs-text);">{{ $ticket->ticket_number }}</td>
                                                <td style="color: var(--bs-text);">{{ $ticket->service->name }}</td>
                                                <td style="color: var(--bs-text);">{{ $ticket->user->name }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'verified' => 'info',
                                                            'processing' => 'primary',
                                                            'completed' => 'success',
                                                            'rejected' => 'danger',
                                                            'pending_approval' => 'secondary'
                                                        ];
                                                        $color = $statusColors[$ticket->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                                </td>
                                                <td style="color: var(--bs-text);">{{ $ticket->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center" style="color: var(--bs-text);">Tidak ada tiket layanan terbaru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="visitors" role="tabpanel" aria-labelledby="visitors-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: var(--bs-text);"><i class="bi bi-person-vcard me-1"></i>Nama</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-building me-1"></i>Institusi</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-bullseye me-1"></i>Tujuan</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-door-open me-1"></i>Waktu Masuk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentVisitors as $visitor)
                                            <tr>
                                                <td style="color: var(--bs-text);">{{ $visitor->name }}</td>
                                                <td style="color: var(--bs-text);">{{ $visitor->institution }}</td>
                                                <td style="color: var(--bs-text);">{{ $visitor->purpose }}</td>
                                                <td style="color: var(--bs-text);">{{ $visitor->check_in_time->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center" style="color: var(--bs-text);">Tidak ada pengunjung terbaru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="complaints" role="tabpanel" aria-labelledby="complaints-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: var(--bs-text);"><i class="bi bi-file-text me-1"></i>Judul</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-tags me-1"></i>Tipe</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-check-circle me-1"></i>Status</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-calendar-event me-1"></i>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentComplaints as $complaint)
                                            <tr>
                                                <td style="color: var(--bs-text);">{{ $complaint->title }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $complaint->complaint_type === 'pengaduan' ? 'danger' : 'warning' }}">
                                                        {{ ucfirst($complaint->complaint_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'in_review' => 'info',
                                                            'resolved' => 'success',
                                                            'rejected' => 'danger'
                                                        ];
                                                        $color = $statusColors[$complaint->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $complaint->status)) }}</span>
                                                </td>
                                                <td style="color: var(--bs-text);">{{ $complaint->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center" style="color: var(--bs-text);">Tidak ada pengaduan terbaru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="surveys" role="tabpanel" aria-labelledby="surveys-tab">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: var(--bs-text);"><i class="bi bi-person-lines-fill me-1"></i>Nama Responden</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-clipboard2-check me-1"></i>Survey</th>
                                            <th style="color: var(--bs-text);"><i class="bi bi-calendar2-check me-1"></i>Waktu Mengisi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentSurveyRespondents as $response)
                                            <tr>
                                                <td style="color: var(--bs-text);">{{ $response->user->name ?? 'Anonim' }}</td>
                                                <td style="color: var(--bs-text);">{{ $response->survey->name }}</td>
                                                <td style="color: var(--bs-text);">{{ $response->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center" style="color: var(--bs-text);">Tidak ada responden survei terbaru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Stats Card Improvements - Light Mode */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #14532d 0%, #16a34a 100%) !important;
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    }

    .card {
        border-radius: 12px !important;
    }

    .text-white {
        color: #ffffff !important;
    }

    .bg-white {
        background-color: rgba(255, 255, 255, 0.25) !important;
    }

    .bg-opacity-20 {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }

    .bg-opacity-25 {
        background-color: rgba(255, 255, 255, 0.25) !important;
    }

    .bg-opacity-50 {
        background-color: rgba(255, 255, 255, 0.5) !important;
    }

    /* Stats Card Styles */
    .stats-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: none !important;
    }

    .stats-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
    }

    /* Table Row Hover */
    .table-hover tbody tr {
        transition: all 0.3s ease;
    }

    .table-hover tbody tr:hover {
        background-color: var(--bs-gray-100) !important;
        transform: scale(1.01);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Card Hover Effects */
    .card:not(.stats-card) {
        transition: all 0.3s ease;
    }

    .card:not(.stats-card):hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    /* Nav Tabs */
    .nav-tabs .nav-link {
        color: var(--bs-gray-600);
        border: none;
        background: transparent;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: var(--bs-primary);
        background-color: var(--bs-gray-100);
    }

    .nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        background-color: transparent;
        border-bottom: 3px solid var(--bs-primary);
        font-weight: 600;
    }

    /* Badge Improvements */
    .badge {
        font-weight: 600;
        padding: 0.5em 0.8em;
        border-radius: 0.375rem;
    }

    /* Dark Mode Adaptations */
    [data-theme="dark"] .bg-gradient-primary {
        background: linear-gradient(135deg, #166534 0%, #22c55e 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-success {
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
    }

    [data-theme="dark"] .bg-gradient-info {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
    }

    [data-theme="dark"] .stats-card:hover {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5) !important;
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--bs-gray-700) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .card:not(.stats-card):hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4) !important;
    }

    [data-theme="dark"] .nav-tabs .nav-link:hover {
        color: #22c55e;
        background-color: var(--bs-gray-700);
    }

    [data-theme="dark"] .nav-tabs .nav-link.active {
        color: #22c55e !important;
        border-bottom-color: #22c55e;
    }

    [data-theme="dark"] .card-header {
        background-color: var(--bs-gray-700) !important;
    }

    /* List Group Items */
    .list-group-item {
        background-color: transparent;
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: var(--bs-gray-100);
    }

    [data-theme="dark"] .list-group-item:hover {
        background-color: var(--bs-gray-700);
    }

    /* Status Cards Hover Effects */
    .hover-lift {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
    }

    [data-theme="dark"] .hover-lift:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Custom grid for 5 columns */
    .col-xl-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }

    @media (max-width: 1199.98px) {
        .col-xl-2-4 {
            width: 33.333333%;
        }
    }

    @media (max-width: 991.98px) {
        .col-xl-2-4 {
            width: 50%;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-2-4 {
            width: 100%;
        }
    }

    /* Icon background utilities */
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    /* Section Headers */
    .row.mb-4 h5 {
        font-size: 1.1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--bs-border);
    }

    /* Status card text colors for dark mode */
    [data-theme="dark"] .text-muted {
        color: var(--bs-gray-400) !important;
    }

    /* List Item Hover Effects */
    .list-item-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .list-item-hover:hover {
        background-color: var(--bs-gray-100) !important;
        transform: translateX(5px);
        border-left: 3px solid var(--bs-primary) !important;
        padding-left: calc(1rem - 3px) !important;
    }

    [data-theme="dark"] .list-item-hover:hover {
        background-color: var(--bs-gray-700) !important;
        border-left-color: #22c55e !important;
    }

    /* Card Chart Headers */
    .card-header {
        transition: all 0.3s ease;
    }

    .hover-lift:hover .card-header {
        background: linear-gradient(135deg, var(--bs-surface) 0%, var(--bs-gray-100) 100%) !important;
    }

    [data-theme="dark"] .hover-lift:hover .card-header {
        background: linear-gradient(135deg, var(--bs-gray-700) 0%, var(--bs-gray-600) 100%) !important;
    }

    /* Icon animations */
    .list-item-hover i {
        transition: all 0.3s ease;
    }

    .list-item-hover:hover i {
        transform: scale(1.2);
    }

    /* Force icon visibility */
    i[class*="bi-"], i[class*="fa-"] {
        display: inline-block !important;
        font-style: normal !important;
        font-variant: normal !important;
        text-rendering: auto !important;
        -webkit-font-smoothing: antialiased !important;
        vertical-align: middle;
    }

    /* Icon sizes */
    .fs-4 {
        font-size: 1.5rem !important;
    }

    .fs-5 {
        font-size: 1.25rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Bootstrap Icons is loaded
    const testIcon = document.createElement('i');
    testIcon.className = 'bi bi-star-fill';
    document.body.appendChild(testIcon);

    const iconStyle = window.getComputedStyle(testIcon);
    const iconFont = iconStyle.getPropertyValue('font-family');

    if (!iconFont.includes('bootstrap-icons')) {
        console.warn('⚠️ Bootstrap Icons may not be loaded properly!');
        console.log('Font family:', iconFont);
    } else {
        console.log('✅ Bootstrap Icons loaded successfully!');
    }

    document.body.removeChild(testIcon);

    // Get current theme
    const getCurrentTheme = () => {
        return document.documentElement.getAttribute('data-theme') || 'light';
    };

    // Get colors based on theme
    const getThemeColors = () => {
        const theme = getCurrentTheme();
        const isDark = theme === 'dark';

        return {
            textColor: isDark ? '#f9fafb' : '#1f2937',
            gridColor: isDark ? '#374151' : '#e5e7eb',
            backgroundColor: isDark ? '#1f2937' : '#ffffff'
        };
    };

    // Survey Analytics Charts with Filter (Global scope)
    const createSurveyAnalyticsCharts = (filter = 'month') => {
        console.log('Creating survey analytics charts with filter:', filter);
        const themeColors = getThemeColors();

        fetch(`{{ route('suadmin.api.survey-analytics-data') }}?filter=${filter}`)
            .then(response => {
                console.log('Survey analytics response:', response);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Survey analytics data:', data);

                // Update summary cards
                const totalRespondentsEl = document.getElementById('totalRespondents');
                const satisfactionIndexEl = document.getElementById('satisfactionIndex');
                const avgRatingEl = document.getElementById('avgRating');

                if (totalRespondentsEl) totalRespondentsEl.textContent = data.respondents;
                if (satisfactionIndexEl) satisfactionIndexEl.textContent = data.satisfactionIndex;
                if (avgRatingEl) avgRatingEl.textContent = data.avgRating;

                // Response Trend Chart
                const responseTrendCanvas = document.getElementById('responseTrendChart');
                if (responseTrendCanvas) {
                    if (window.responseTrendChart) window.responseTrendChart.destroy();
                    window.responseTrendChart = new Chart(responseTrendCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.responseTrend.labels,
                            datasets: [{
                                label: 'Jumlah Responden',
                                data: data.responseTrend.data,
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {display: false},
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ctx.parsed.y + ' responden'
                                    }
                                }
                            },
                            scales: {
                                y: {beginAtZero: true, grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, stepSize: 1}},
                                x: {grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor}}
                            }
                        }
                    });
                }

                // Age Distribution Chart (Doughnut)
                const ageCanvas = document.getElementById('ageDistributionChart');
                if (ageCanvas) {
                    if (window.ageChart) window.ageChart.destroy();
                    window.ageChart = new Chart(ageCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: data.ageDistribution.labels,
                            datasets: [{
                                data: data.ageDistribution.data,
                                backgroundColor: ['#f59e0b', '#3b82f6', '#22c55e', '#ef4444', '#8b5cf6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {position: 'bottom', labels: {color: themeColors.textColor, font: {size: 11}}}
                            }
                        }
                    });
                }

                // Occupation Distribution Chart (Pie)
                const occupationCanvas = document.getElementById('occupationDistributionChart');
                if (occupationCanvas && data.occupationDistribution.labels.length > 0) {
                    if (window.occupationChart) window.occupationChart.destroy();
                    window.occupationChart = new Chart(occupationCanvas.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: data.occupationDistribution.labels,
                            datasets: [{
                                data: data.occupationDistribution.data,
                                backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {position: 'bottom', labels: {color: themeColors.textColor, font: {size: 11}}}
                            }
                        }
                    });
                }

                // Education Distribution Chart (Doughnut)
                const educationCanvas = document.getElementById('educationDistributionChart');
                if (educationCanvas && data.educationDistribution.labels.length > 0) {
                    if (window.educationChart) window.educationChart.destroy();
                    window.educationChart = new Chart(educationCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: data.educationDistribution.labels,
                            datasets: [{
                                data: data.educationDistribution.data,
                                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {position: 'bottom', labels: {color: themeColors.textColor, font: {size: 11}}}
                            }
                        }
                    });
                }

                // Service Distribution Chart (Bar)
                const serviceCanvas = document.getElementById('serviceDistributionChart');
                if (serviceCanvas && data.serviceDistribution.labels.length > 0) {
                    if (window.serviceDistChart) window.serviceDistChart.destroy();
                    window.serviceDistChart = new Chart(serviceCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: data.serviceDistribution.labels,
                            datasets: [{
                                label: 'Jumlah Responden',
                                data: data.serviceDistribution.data,
                                backgroundColor: '#3b82f6',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {display: false},
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ctx.parsed.y + ' responden'
                                    }
                                }
                            },
                            scales: {
                                y: {beginAtZero: true, grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, stepSize: 1}},
                                x: {grid: {display: false}, ticks: {color: themeColors.textColor}}
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching survey analytics data:', error);
                console.error('Error details:', error.message);
            });
    };

    // Chart configurations
    const createCharts = () => {
        const themeColors = getThemeColors();

        // Service Performance Chart with Filter
        const createServicePerformanceChart = (filter = 'day') => {
            const servicePerformanceCanvas = document.getElementById('servicePerformanceChart');
            if (!servicePerformanceCanvas) return;

            // Fetch data from API
            fetch(`{{ route('suadmin.api.service-performance-data') }}?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    const serviceCtx = servicePerformanceCanvas.getContext('2d');

                    // Destroy existing chart if it exists
                    if (window.serviceChart) {
                        window.serviceChart.destroy();
                    }

                    window.serviceChart = new Chart(serviceCtx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Tiket Masuk',
                                    data: data.datasets.incoming,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#f59e0b',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#f59e0b'
                                },
                                {
                                    label: 'Sedang Diproses',
                                    data: data.datasets.processing,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#3b82f6',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#3b82f6'
                                },
                                {
                                    label: 'Tiket Selesai',
                                    data: data.datasets.completed,
                                    borderColor: '#22c55e',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#22c55e',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#22c55e'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        color: themeColors.textColor,
                                        font: {
                                            size: 11,
                                            weight: '500'
                                        },
                                        usePointStyle: true,
                                        padding: 10
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' tiket';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: themeColors.gridColor
                                    },
                                    ticks: {
                                        color: themeColors.textColor,
                                        stepSize: 1
                                    }
                                },
                                x: {
                                    grid: {
                                        color: themeColors.gridColor
                                    },
                                    ticks: {
                                        color: themeColors.textColor,
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching service performance data:', error);
                });
        };

        // Initialize chart with default filter
        createServicePerformanceChart('day');

        // Handle filter change
        const filterSelect = document.getElementById('servicePerformanceFilter');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                createServicePerformanceChart(this.value);
            });
        }

        // Complaint Performance Chart with Filter
        const createComplaintPerformanceChart = (filter = 'day') => {
            const complaintPerformanceCanvas = document.getElementById('complaintPerformanceChart');
            if (!complaintPerformanceCanvas) return;

            fetch(`{{ route('suadmin.api.complaint-performance-data') }}?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    const complaintCtx = complaintPerformanceCanvas.getContext('2d');

                    if (window.complaintChart) {
                        window.complaintChart.destroy();
                    }

                    window.complaintChart = new Chart(complaintCtx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Pengaduan Masuk',
                                    data: data.datasets.incoming,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#f59e0b',
                                    pointBorderColor: '#fff'
                                },
                                {
                                    label: 'Sedang Ditinjau',
                                    data: data.datasets.processing,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#3b82f6',
                                    pointBorderColor: '#fff'
                                },
                                {
                                    label: 'Selesai',
                                    data: data.datasets.completed,
                                    borderColor: '#22c55e',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#22c55e',
                                    pointBorderColor: '#fff'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        color: themeColors.textColor,
                                        font: { size: 11, weight: '500' },
                                        usePointStyle: true,
                                        padding: 10
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' pengaduan';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: themeColors.gridColor },
                                    ticks: { color: themeColors.textColor, stepSize: 1 }
                                },
                                x: {
                                    grid: { color: themeColors.gridColor },
                                    ticks: { color: themeColors.textColor, maxRotation: 45, minRotation: 0 }
                                }
                            },
                            interaction: { mode: 'index', intersect: false }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching complaint performance data:', error);
                });
        };

        createComplaintPerformanceChart('day');

        const complaintFilterSelect = document.getElementById('complaintPerformanceFilter');
        if (complaintFilterSelect) {
            complaintFilterSelect.addEventListener('change', function() {
                createComplaintPerformanceChart(this.value);
            });
        }

        // Whistleblowing Performance Chart with Filter
        const createWhistleblowingPerformanceChart = (filter = 'day') => {
            const whistleblowingPerformanceCanvas = document.getElementById('whistleblowingPerformanceChart');
            if (!whistleblowingPerformanceCanvas) return;

            fetch(`{{ route('suadmin.api.whistleblowing-performance-data') }}?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    const whistleblowingCtx = whistleblowingPerformanceCanvas.getContext('2d');

                    if (window.whistleblowingChart) {
                        window.whistleblowingChart.destroy();
                    }

                    window.whistleblowingChart = new Chart(whistleblowingCtx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Laporan Masuk',
                                    data: data.datasets.incoming,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#ef4444',
                                    pointBorderColor: '#fff'
                                },
                                {
                                    label: 'Sedang Ditinjau',
                                    data: data.datasets.processing,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#3b82f6',
                                    pointBorderColor: '#fff'
                                },
                                {
                                    label: 'Selesai',
                                    data: data.datasets.completed,
                                    borderColor: '#22c55e',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#22c55e',
                                    pointBorderColor: '#fff'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        color: themeColors.textColor,
                                        font: { size: 11, weight: '500' },
                                        usePointStyle: true,
                                        padding: 10
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' laporan';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: themeColors.gridColor },
                                    ticks: { color: themeColors.textColor, stepSize: 1 }
                                },
                                x: {
                                    grid: { color: themeColors.gridColor },
                                    ticks: { color: themeColors.textColor, maxRotation: 45, minRotation: 0 }
                                }
                            },
                            interaction: { mode: 'index', intersect: false }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching whistleblowing performance data:', error);
                });
        };

        createWhistleblowingPerformanceChart('day');

        const whistleblowingFilterSelect = document.getElementById('whistleblowingPerformanceFilter');
        if (whistleblowingFilterSelect) {
            whistleblowingFilterSelect.addEventListener('change', function() {
                createWhistleblowingPerformanceChart(this.value);
            });
        }

        // Initialize survey analytics charts (function defined at top of script)
        console.log('Initializing survey analytics charts...');
        createSurveyAnalyticsCharts('month');

        // Add filter change listener for survey analytics
        const surveyFilterSelect = document.getElementById('surveyAnalyticsFilter');
        console.log('Survey filter select element:', surveyFilterSelect);
        if (surveyFilterSelect) {
            surveyFilterSelect.addEventListener('change', function() {
                console.log('Survey filter changed to:', this.value);
                createSurveyAnalyticsCharts(this.value);
            });
        }
    };

    // Wait for Chart.js to be available
    if (typeof Chart !== 'undefined') {
        createCharts();

        // Listen for theme changes and update charts
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-theme') {
                    // Get current filter values
                    const serviceFilterSelect = document.getElementById('servicePerformanceFilter');
                    const complaintFilterSelect = document.getElementById('complaintPerformanceFilter');
                    const whistleblowingFilterSelect = document.getElementById('whistleblowingPerformanceFilter');

                    const currentServiceFilter = serviceFilterSelect ? serviceFilterSelect.value : 'day';
                    const currentComplaintFilter = complaintFilterSelect ? complaintFilterSelect.value : 'day';
                    const currentWhistleblowingFilter = whistleblowingFilterSelect ? whistleblowingFilterSelect.value : 'day';

                    // Get survey filter
                    const surveyFilterSelect = document.getElementById('surveyAnalyticsFilter');
                    const currentSurveyFilter = surveyFilterSelect ? surveyFilterSelect.value : 'month';

                    // Destroy existing charts
                    if (window.serviceChart) window.serviceChart.destroy();
                    if (window.complaintChart) window.complaintChart.destroy();
                    if (window.whistleblowingChart) window.whistleblowingChart.destroy();
                    if (window.responseTrendChart) window.responseTrendChart.destroy();
                    if (window.ageChart) window.ageChart.destroy();
                    if (window.occupationChart) window.occupationChart.destroy();
                    if (window.educationChart) window.educationChart.destroy();
                    if (window.serviceDistChart) window.serviceDistChart.destroy();

                    // Recreate charts with new theme
                    createCharts();

                    // Re-create service chart with current filter
                    const themeColors = getThemeColors();
                    const createServicePerformanceChart = (filter = 'day') => {
                        const servicePerformanceCanvas = document.getElementById('servicePerformanceChart');
                        if (!servicePerformanceCanvas) return;

                        fetch(`{{ route('suadmin.api.service-performance-data') }}?filter=${filter}`)
                            .then(response => response.json())
                            .then(data => {
                                const serviceCtx = servicePerformanceCanvas.getContext('2d');
                                if (window.serviceChart) window.serviceChart.destroy();

                                window.serviceChart = new Chart(serviceCtx, {
                                    type: 'line',
                                    data: {
                                        labels: data.labels,
                                        datasets: [
                                            {
                                                label: 'Tiket Masuk',
                                                data: data.datasets.incoming,
                                                borderColor: '#f59e0b',
                                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                                borderWidth: 2,
                                                fill: true,
                                                tension: 0.4,
                                                pointBackgroundColor: '#f59e0b',
                                                pointBorderColor: '#fff'
                                            },
                                            {
                                                label: 'Sedang Diproses',
                                                data: data.datasets.processing,
                                                borderColor: '#3b82f6',
                                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                borderWidth: 2,
                                                fill: true,
                                                tension: 0.4,
                                                pointBackgroundColor: '#3b82f6',
                                                pointBorderColor: '#fff'
                                            },
                                            {
                                                label: 'Tiket Selesai',
                                                data: data.datasets.completed,
                                                borderColor: '#22c55e',
                                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                                borderWidth: 2,
                                                fill: true,
                                                tension: 0.4,
                                                pointBackgroundColor: '#22c55e',
                                                pointBorderColor: '#fff'
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top',
                                                labels: {
                                                    color: themeColors.textColor,
                                                    font: { size: 11, weight: '500' },
                                                    usePointStyle: true,
                                                    padding: 10
                                                }
                                            },
                                            tooltip: {
                                                mode: 'index',
                                                intersect: false,
                                                callbacks: {
                                                    label: function(context) {
                                                        return context.dataset.label + ': ' + context.parsed.y + ' tiket';
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                grid: { color: themeColors.gridColor },
                                                ticks: { color: themeColors.textColor, stepSize: 1 }
                                            },
                                            x: {
                                                grid: { color: themeColors.gridColor },
                                                ticks: { color: themeColors.textColor, maxRotation: 45, minRotation: 0 }
                                            }
                                        },
                                        interaction: { mode: 'index', intersect: false }
                                    }
                                });
                            });
                    };
                    createServicePerformanceChart(currentServiceFilter);

                    // Re-create complaint chart
                    const createComplaintPerformanceChart = (filter = 'day') => {
                        const canvas = document.getElementById('complaintPerformanceChart');
                        if (!canvas) return;
                        fetch(`{{ route('suadmin.api.complaint-performance-data') }}?filter=${filter}`)
                            .then(response => response.json())
                            .then(data => {
                                const ctx = canvas.getContext('2d');
                                if (window.complaintChart) window.complaintChart.destroy();
                                window.complaintChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: data.labels,
                                        datasets: [
                                            {label: 'Pengaduan Masuk', data: data.datasets.incoming, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#f59e0b', pointBorderColor: '#fff'},
                                            {label: 'Sedang Ditinjau', data: data.datasets.processing, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#3b82f6', pointBorderColor: '#fff'},
                                            {label: 'Selesai', data: data.datasets.completed, borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#22c55e', pointBorderColor: '#fff'}
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {display: true, position: 'top', labels: {color: themeColors.textColor, font: {size: 11, weight: '500'}, usePointStyle: true, padding: 10}},
                                            tooltip: {mode: 'index', intersect: false, callbacks: {label: function(context) { return context.dataset.label + ': ' + context.parsed.y + ' pengaduan'; }}}
                                        },
                                        scales: {
                                            y: {beginAtZero: true, grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, stepSize: 1}},
                                            x: {grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, maxRotation: 45, minRotation: 0}}
                                        },
                                        interaction: {mode: 'index', intersect: false}
                                    }
                                });
                            });
                    };
                    createComplaintPerformanceChart(currentComplaintFilter);

                    // Re-create whistleblowing chart
                    const createWhistleblowingPerformanceChart = (filter = 'day') => {
                        const canvas = document.getElementById('whistleblowingPerformanceChart');
                        if (!canvas) return;
                        fetch(`{{ route('suadmin.api.whistleblowing-performance-data') }}?filter=${filter}`)
                            .then(response => response.json())
                            .then(data => {
                                const ctx = canvas.getContext('2d');
                                if (window.whistleblowingChart) window.whistleblowingChart.destroy();
                                window.whistleblowingChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: data.labels,
                                        datasets: [
                                            {label: 'Laporan Masuk', data: data.datasets.incoming, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#ef4444', pointBorderColor: '#fff'},
                                            {label: 'Sedang Ditinjau', data: data.datasets.processing, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#3b82f6', pointBorderColor: '#fff'},
                                            {label: 'Selesai', data: data.datasets.completed, borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointBackgroundColor: '#22c55e', pointBorderColor: '#fff'}
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {display: true, position: 'top', labels: {color: themeColors.textColor, font: {size: 11, weight: '500'}, usePointStyle: true, padding: 10}},
                                            tooltip: {mode: 'index', intersect: false, callbacks: {label: function(context) { return context.dataset.label + ': ' + context.parsed.y + ' laporan'; }}}
                                        },
                                        scales: {
                                            y: {beginAtZero: true, grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, stepSize: 1}},
                                            x: {grid: {color: themeColors.gridColor}, ticks: {color: themeColors.textColor, maxRotation: 45, minRotation: 0}}
                                        },
                                        interaction: {mode: 'index', intersect: false}
                                    }
                                });
                            });
                    };
                    createWhistleblowingPerformanceChart(currentWhistleblowingFilter);

                    // Re-create survey analytics charts
                    createSurveyAnalyticsCharts(currentSurveyFilter);
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
    } else {
        console.error('Chart.js library not loaded. Please ensure Chart.js is included in your dependencies.');
    }
});
</script>
@endpush