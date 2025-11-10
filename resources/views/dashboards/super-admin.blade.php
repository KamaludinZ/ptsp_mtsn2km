@extends('layouts.app')

@section('content')
<div class="dashboard-container py-4 px-3 px-lg-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                        <i class="fas fa-tachometer-alt me-2"></i>Super Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">Overview sistem keseluruhan PTSP MTsN 2 Kota Malang</p>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-2">
                        <i class="fas fa-circle me-2" style="font-size: 0.6rem;"></i>System Online
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Total Users</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_users'] }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> {{ $stats['active_users'] }} active
                            </small>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-users fa-2x" style="color: var(--bs-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Services -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Total Services</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_services'] }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> {{ $stats['active_services'] }} active
                            </small>
                        </div>
                        <div class="icon-box rounded-3 p-3" style="background: rgba(147, 51, 234, 0.1);">
                            <i class="fas fa-concierge-bell fa-2x" style="color: #9333ea;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Total Tickets</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_tickets'] }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> {{ $stats['pending_tickets'] }} pending
                            </small>
                        </div>
                        <div class="icon-box rounded-3 p-3" style="background: rgba(234, 88, 12, 0.1);">
                            <i class="fas fa-ticket-alt fa-2x" style="color: var(--bs-secondary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Visitors -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 small">Active Visitors</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['active_visitors'] }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-chart-line"></i> {{ $stats['total_visitors'] }} total today
                            </small>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-user-friends fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Status Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-white-50 small fw-semibold">PENDING TICKETS</p>
                            <h2 class="mb-0 fw-bold">{{ $stats['pending_tickets'] }}</h2>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-white-50 small fw-semibold">IN PROGRESS</p>
                            <h2 class="mb-0 fw-bold">{{ $stats['in_progress_tickets'] }}</h2>
                        </div>
                        <i class="fas fa-spinner fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-white-50 small fw-semibold">COMPLETED</p>
                            <h2 class="mb-0 fw-bold">{{ $stats['completed_tickets'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Performance & Channel Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-day text-primary me-2"></i>Kinerja Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <i class="fas fa-plus-circle fa-2x text-primary mb-3"></i>
                            <h3 class="fw-bold mb-1">{{ $stats['tickets_today'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Tiket Masuk Hari Ini</p>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-check-double fa-2x text-success mb-3"></i>
                            <h3 class="fw-bold mb-1">{{ $stats['completed_today'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Tiket Selesai Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-exchange-alt" style="color: var(--bs-secondary);"></i>
                        <span class="ms-2">Tiket per Channel</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <i class="fas fa-globe fa-2x text-info mb-3"></i>
                            <h3 class="fw-bold mb-1">{{ $stats['tickets_online'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Online (Portal)</p>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-store fa-2x" style="color: var(--bs-secondary);" class="mb-3"></i>
                            <h3 class="fw-bold mb-1">{{ $stats['tickets_offline'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Offline (Loket)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaints, Visitors & Surveys Summary -->
    <div class="row g-4 mb-4">
        <!-- Complaints & Whistleblowing Stats -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>Pengaduan & Whistleblowing
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-12">
                            <h2 class="fw-bold mb-0" style="color: var(--bs-secondary);">{{ $complaintStats['total'] }}</h2>
                            <p class="text-muted small mb-0">Total Pengaduan</p>
                        </div>
                    </div>
                    <div class="row text-center mb-3 py-3 border-top border-bottom">
                        <div class="col-6 border-end">
                            <h4 class="fw-bold mb-1">{{ $complaintStats['this_month'] }}</h4>
                            <p class="text-muted small mb-0">Bulan Ini</p>
                        </div>
                        <div class="col-6">
                            <h4 class="fw-bold mb-1">{{ $complaintStats['this_quarter'] }}</h4>
                            <p class="text-muted small mb-0">Triwulan Ini</p>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>{{ $complaintStats['resolved'] }}
                            </span>
                            <p class="text-muted small mb-0 mt-2">Selesai</p>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                <i class="fas fa-clock me-1"></i>{{ $complaintStats['pending'] }}
                            </span>
                            <p class="text-muted small mb-0 mt-2">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Book Stats -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-book text-info me-2"></i>Buku Tamu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-12">
                            <h2 class="fw-bold mb-0 text-info">{{ $visitorStats['total'] }}</h2>
                            <p class="text-muted small mb-0">Total Pengunjung</p>
                        </div>
                    </div>
                    <div class="row text-center mb-3 py-3 border-top border-bottom">
                        <div class="col-6 border-end">
                            <h4 class="fw-bold mb-1">{{ $visitorStats['this_month'] }}</h4>
                            <p class="text-muted small mb-0">Bulan Ini</p>
                        </div>
                        <div class="col-6">
                            <h4 class="fw-bold mb-1">{{ $visitorStats['this_quarter'] }}</h4>
                            <p class="text-muted small mb-0">Triwulan Ini</p>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-chart-line text-info me-2"></i>
                                <span class="fw-bold">{{ number_format($visitorStats['average_daily'], 1) }}</span>
                                <span class="text-muted small ms-2">rata-rata per hari</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Survey Stats (SKM) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-poll text-success me-2"></i>Survei Kepuasan (SKM)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-12">
                            <h2 class="fw-bold mb-0 text-success">{{ number_format($surveyStats['average_score'], 2) }}</h2>
                            <p class="text-muted small mb-0">Rata-rata Skor</p>
                        </div>
                    </div>
                    <div class="row text-center mb-3 py-3 border-top border-bottom">
                        <div class="col-6 border-end">
                            <h4 class="fw-bold mb-1">{{ $surveyStats['this_month'] }}</h4>
                            <p class="text-muted small mb-0">Bulan Ini</p>
                        </div>
                        <div class="col-6">
                            <h4 class="fw-bold mb-1">{{ $surveyStats['this_quarter'] }}</h4>
                            <p class="text-muted small mb-0">Triwulan Ini</p>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-12">
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-users me-1"></i>{{ $surveyStats['total_responses'] }} Responden
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health & Security -->
    <div class="row g-4 mb-4">
        <!-- System Health -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-heartbeat text-success me-2"></i>System Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-database me-2 text-primary"></i>Database</span>
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>{{ ucfirst($systemHealth['database_status']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Cache</span>
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>{{ ucfirst($systemHealth['cache_status']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-tasks me-2 text-info"></i>Queue</span>
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>{{ ucfirst($systemHealth['queue_status']) }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="fw-semibold"><i class="fas fa-hdd me-2" style="color: #9333ea;"></i>Storage</span>
                            <div class="text-end">
                                <small class="text-muted d-block">
                                    {{ $systemHealth['storage_usage']['used'] }} / {{ $systemHealth['storage_usage']['total'] }}
                                </small>
                                <div class="progress mt-2" style="width: 150px; height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $systemHealth['storage_usage']['percentage'] }}%"
                                         aria-valuenow="{{ $systemHealth['storage_usage']['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Overview -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-shield-alt text-danger me-2"></i>Security Overview
                    </h5>
                    <a href="{{ route('admin.security.dashboard') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View Details
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Failed Logins Today</span>
                            <span class="badge {{ $securityMetrics['failed_logins_today'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-3 py-2">
                                {{ $securityMetrics['failed_logins_today'] }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-ban me-2 text-warning"></i>Blocked IPs</span>
                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                {{ $securityMetrics['blocked_ips'] }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom">
                            <span class="fw-semibold"><i class="fas fa-user-secret me-2 text-info"></i>Suspicious Activities</span>
                            <span class="badge {{ $securityMetrics['suspicious_activities'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} px-3 py-2">
                                {{ $securityMetrics['suspicious_activities'] }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="fw-semibold"><i class="fas fa-clock me-2" style="color: #9333ea;"></i>Last Security Scan</span>
                            <small class="text-muted">{{ $securityMetrics['last_security_scan'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row g-4 mb-4">
        <!-- Ticket Performance Chart -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-chart-line text-primary me-2"></i>Performa Tiket
                        </h5>
                        <select id="ticketChartPeriod" class="form-select form-select-sm w-auto">
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Triwulanan</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ticketChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Complaints Chart -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>Tren Pengaduan
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="complaintsChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Visitors Chart -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users text-info me-2"></i>Tren Pengunjung
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="visitorsChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Survey Scores Chart -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-poll text-success me-2"></i>Skor Survei Kepuasan
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="surveyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.services.index') }}" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(147, 51, 234, 0.05);">
                            <i class="fas fa-concierge-bell fa-2x mb-2" style="color: #9333ea;"></i>
                            <small class="fw-semibold d-block">Manage Services</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('backoffice.tickets.all') }}" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(59, 130, 246, 0.05);">
                            <i class="fas fa-ticket-alt fa-2x mb-2" style="color: #3b82f6;"></i>
                            <small class="fw-semibold d-block">View All Tickets</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.security.dashboard') }}" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(239, 68, 68, 0.05);">
                            <i class="fas fa-shield-alt fa-2x mb-2" style="color: #ef4444;"></i>
                            <small class="fw-semibold d-block">Security</small>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.security.maintenance') }}" class="text-decoration-none">
                        <div class="card border-0 h-100 text-center p-3 quick-action-card" style="background: rgba(245, 158, 11, 0.05);">
                            <i class="fas fa-tools fa-2x mb-2" style="color: #f59e0b;"></i>
                            <small class="fw-semibold d-block">Maintenance</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets & Users -->
    <div class="row g-4">
        <!-- Recent Tickets -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-history text-primary me-2"></i>Recent Tickets
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentTickets->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTickets as $ticket)
                                <div class="list-group-item px-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">{{ $ticket->ticket_number }}</h6>
                                            <p class="mb-1 text-muted small">{{ $ticket->service->name ?? 'N/A' }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>{{ $ticket->user->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge
                                                @if($ticket->status === 'completed') bg-success-subtle text-success
                                                @elseif($ticket->status === 'in_progress') bg-info-subtle text-info
                                                @else bg-warning-subtle text-warning
                                                @endif px-3 py-2">
                                                {{ ucfirst($ticket->status) }}
                                            </span>
                                            <small class="d-block text-muted mt-1">{{ $ticket->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No recent tickets</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-plus text-success me-2"></i>Recent Users
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentUsers as $user)
                                <div class="list-group-item px-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="avatar-circle bg-primary bg-opacity-10 me-3">
                                                <span class="fw-bold" style="color: var(--bs-primary);">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $user->name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info-subtle text-info px-3 py-2">
                                                {{ ucfirst($user->user_type) }}
                                            </span>
                                            <small class="d-block text-muted mt-1">{{ $user->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No recent users</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Container with Max Width */
    .dashboard-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    /* Center content for very large screens */
    @media (min-width: 1800px) {
        .dashboard-container {
            max-width: 100%;
            margin: 0 auto;
        }
    }

    /* Hover effects */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg) !important;
    }

    .quick-action-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    /* Avatar circle */
    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Card shadows */
    .shadow-sm {
        box-shadow: var(--shadow-sm) !important;
    }
    .shadow {
        box-shadow: var(--shadow-md) !important;
    }

    /* Badge styling */
    .bg-success-subtle {
        background-color: rgba(16, 185, 129, 0.1) !important;
    }
    .bg-warning-subtle {
        background-color: rgba(245, 158, 11, 0.1) !important;
    }
    .bg-danger-subtle {
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    .bg-info-subtle {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }

    /* Optimize card spacing for large screens */
    @media (min-width: 1400px) {
        .g-4 {
            --bs-gutter-x: 1.75rem;
            --bs-gutter-y: 1.75rem;
        }
    }
</style>

<!-- Chart.js Library (bundled locally) -->
@vite(['resources/js/chart-bundle.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance data from controller
    const performanceData = @json($performanceData);

    // Chart color scheme matching dashboard theme
    const colors = {
        primary: '#14532d',
        secondary: '#ea580c',
        success: '#10b981',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
    };

    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    };

    // 1. Ticket Performance Chart with Period Toggle
    let ticketChart;
    const ticketChartCanvas = document.getElementById('ticketChart');

    function renderTicketChart(period = 'monthly') {
        const data = period === 'monthly' ? performanceData.monthly_tickets : performanceData.quarterly_tickets;
        const labels = data.map(d => period === 'monthly' ? d.month_name : d.quarter_name);

        if (ticketChart) {
            ticketChart.destroy();
        }

        ticketChart = new Chart(ticketChartCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Tiket',
                        data: data.map(d => d.total),
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Selesai',
                        data: data.map(d => d.completed),
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    title: {
                        display: true,
                        text: period === 'monthly' ? 'Performa Bulanan Tahun ' + new Date().getFullYear() : 'Performa Triwulanan Tahun ' + new Date().getFullYear()
                    }
                }
            }
        });
    }

    // Initialize with monthly view
    renderTicketChart('monthly');

    // Period toggle handler
    document.getElementById('ticketChartPeriod').addEventListener('change', function(e) {
        renderTicketChart(e.target.value);
    });

    // 2. Complaints Chart
    const complaintsChart = new Chart(document.getElementById('complaintsChart'), {
        type: 'bar',
        data: {
            labels: performanceData.monthly_complaints.map(d => d.month_name),
            datasets: [
                {
                    label: 'Total Pengaduan',
                    data: performanceData.monthly_complaints.map(d => d.total),
                    backgroundColor: colors.secondary + 'cc',
                    borderColor: colors.secondary,
                    borderWidth: 1
                },
                {
                    label: 'Selesai',
                    data: performanceData.monthly_complaints.map(d => d.resolved),
                    backgroundColor: colors.success + 'cc',
                    borderColor: colors.success,
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    text: 'Pengaduan Bulanan Tahun ' + new Date().getFullYear()
                }
            }
        }
    });

    // 3. Visitors Chart
    const visitorsChart = new Chart(document.getElementById('visitorsChart'), {
        type: 'line',
        data: {
            labels: performanceData.monthly_visitors.map(d => d.month_name),
            datasets: [{
                label: 'Jumlah Pengunjung',
                data: performanceData.monthly_visitors.map(d => d.total),
                borderColor: colors.info,
                backgroundColor: colors.info + '20',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    text: 'Tren Pengunjung Bulanan Tahun ' + new Date().getFullYear()
                }
            }
        }
    });

    // 4. Survey Scores Chart
    const surveyChart = new Chart(document.getElementById('surveyChart'), {
        type: 'line',
        data: {
            labels: performanceData.monthly_surveys.map(d => d.month_name),
            datasets: [
                {
                    label: 'Rata-rata Skor',
                    data: performanceData.monthly_surveys.map(d => d.average_score),
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Responden',
                    data: performanceData.monthly_surveys.map(d => d.total),
                    borderColor: colors.warning,
                    backgroundColor: colors.warning + '20',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
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
                },
                title: {
                    display: true,
                    text: 'Survei Kepuasan Bulanan Tahun ' + new Date().getFullYear()
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Skor'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: 'Responden'
                    }
                }
            }
        }
    });
});
</script>
@endsection
