@extends('layouts.admin')

@section('title', 'Pengaturan Mode Perawatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Mode Perawatan Sistem</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Status Mode Perawatan</h5>
                                    <div id="maintenance-status" class="mt-3">
                                        <span class="badge bg-success" id="status-badge">Tidak Aktif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Waktu Perawatan Terakhir</h5>
                                    <div class="mt-3" id="last-maintenance">
                                        Tidak pernah
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Pengaturan Mode Perawatan</h5>
                                </div>
                                <div class="card-body">
                                    <form id="maintenance-form">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Pesan Perawatan</label>
                                            <input type="text" class="form-control" id="message" name="message"
                                                   placeholder="Masukkan pesan yang akan ditampilkan saat mode perawatan aktif">
                                        </div>
                                        <div class="mb-3">
                                            <label for="retry" class="form-label">Waktu Pengulangan (detik)</label>
                                            <input type="number" class="form-control" id="retry" name="retry"
                                                   placeholder="Waktu dalam detik sebelum klien mencoba kembali" value="300">
                                        </div>
                                        <div class="mb-3">
                                            <label for="secret" class="form-label">Kode Akses Rahasia (opsional)</label>
                                            <input type="text" class="form-control" id="secret" name="secret"
                                                   placeholder="Kode untuk mengakses aplikasi selama mode perawatan">
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn btn-success" id="enable-btn">
                                                <i class="fas fa-cog"></i> Aktifkan Mode Perawatan
                                            </button>
                                            <button type="button" class="btn btn-warning" id="disable-btn">
                                                <i class="fas fa-times-circle"></i> Nonaktifkan Mode Perawatan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Informasi Mode Perawatan</h6>
                                        <p class="mb-2">Mode perawatan akan menampilkan pesan kepada pengguna bahwa sistem sedang dalam perawatan.</p>
                                        <p class="mb-0">Hanya administrator yang dapat mengakses sistem selama mode perawatan aktif.</p>
                                    </div>
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Peringatan!</h6>
                                        <p class="mb-0">Aktifkan mode perawatan hanya saat Anda benar-benar melakukan pemeliharaan sistem.</p>
                                    </div>
                                </div>
                            </div>
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
    $(document).ready(function() {
        // Check maintenance status on page load
        checkMaintenanceStatus();

        // Check maintenance status periodically
        setInterval(checkMaintenanceStatus, 10000);

        // Enable maintenance mode
        $('#enable-btn').click(function(e) {
            e.preventDefault();

            const formData = {
                message: $('#message').val(),
                retry: $('#retry').val(),
                secret: $('#secret').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: '{{ route("admin.security.maintenance.enable") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                        checkMaintenanceStatus();
                    } else {
                        showAlert('error', response.message || 'Gagal mengaktifkan mode perawatan');
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengaktifkan mode perawatan';
                    showAlert('error', error);
                }
            });
        });

        // Disable maintenance mode
        $('#disable-btn').click(function() {
            if(!confirm('Anda yakin ingin menonaktifkan mode perawatan?')) {
                return;
            }

            $.ajax({
                url: '{{ route("admin.security.maintenance.disable") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                        checkMaintenanceStatus();
                    } else {
                        showAlert('error', response.message || 'Gagal menonaktifkan mode perawatan');
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Terjadi kesalahan saat menonaktifkan mode perawatan';
                    showAlert('error', error);
                }
            });
        });

        function checkMaintenanceStatus() {
            // Get maintenance status using a simple AJAX call to check if app is down for maintenance
            $.get('/api/maintenance-status')
                .done(function(data) {
                    const isDown = data.isDown;
                    const statusBadge = $('#status-badge');

                    if(isDown) {
                        statusBadge.removeClass('bg-success').addClass('bg-danger').text('Aktif');
                    } else {
                        statusBadge.removeClass('bg-danger').addClass('bg-success').text('Tidak Aktif');
                    }
                })
                .fail(function() {
                    // Fallback - check if the maintenance status API is blocked by maintenance mode
                    // If the request fails, it might be because we're in maintenance mode
                    // In which case, we assume maintenance mode is active
                    const statusBadge = $('#status-badge');
                    statusBadge.removeClass('bg-success').addClass('bg-danger').text('Aktif');
                });
        }

        function showAlert(type, message) {
            // Create alert element
            const alertHtml = `
                <div class="alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            // Insert before content
            $('.card-body').first().before(alertHtml);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }
    });
</script>
@endpush
