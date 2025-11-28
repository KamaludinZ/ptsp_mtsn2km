@extends('frontdesk.layout')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-2" style="color: var(--bs-primary); font-weight: 700;">
                <i class="fas fa-sign-out-alt me-2"></i>Visitor Check-out
            </h1>
            <p class="text-muted mb-0">Proses checkout pengunjung yang telah selesai berkunjung</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Check-out Form -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-barcode text-primary me-2"></i>Scan Kartu Pengunjung
                    </h5>
                </div>
                <div class="card-body">
                    <form id="checkoutForm" method="POST" action="{{ route('frontdesk.visitor.checkout') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="visitor_card_number" class="form-label fw-semibold">Nomor Kartu Pengunjung</label>
                            <input type="text"
                                   name="visitor_card_number"
                                   id="visitor_card_number"
                                   class="form-control form-control-lg @error('visitor_card_number') is-invalid @enderror"
                                   placeholder="Masukkan nomor kartu"
                                   required
                                   autofocus>
                            @error('visitor_card_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if(session('error'))
                                <div class="alert alert-danger mt-2">
                                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success mt-2">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-lg btn-primary w-100" onclick="showCheckoutConfirmation()">
                            <i class="fas fa-sign-out-alt me-2"></i>Check-out Pengunjung
                        </button>
                    </form>

                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Tips:</strong> Scan barcode pada kartu pengunjung atau ketik manual nomor kartunya
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Visitors List -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-users text-success me-2"></i>Pengunjung Aktif
                        </h5>
                        <span class="badge bg-success-subtle text-success px-3 py-2">
                            {{ count($activeVisitors ?? []) }} Aktif
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Institusi</th>
                                    <th>No. Kartu</th>
                                    <th>Check-in</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeVisitors ?? [] as $visitor)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary bg-opacity-10 me-2" style="width: 36px; height: 36px;">
                                                    <span class="fw-bold" style="color: var(--bs-primary); font-size: 0.875rem;">
                                                        {{ strtoupper(substr($visitor->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ $visitor->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $visitor->institution ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">{{ $visitor->visitor_card_number }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>{{ $visitor->check_in_time->format('H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="quickCheckout('{{ $visitor->visitor_card_number }}', '{{ $visitor->name }}')">
                                                <i class="fas fa-sign-out-alt me-1"></i>Checkout
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-25 d-block"></i>
                                            <p class="mb-0">Tidak ada pengunjung aktif saat ini</p>
                                        </td>
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

<!-- Checkout Confirmation Modal -->
<div class="modal fade" id="checkoutConfirmModal" tabindex="-1" aria-labelledby="checkoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="checkoutConfirmModalLabel">
                    <i class="fas fa-question-circle text-warning me-2"></i>Konfirmasi Check-out
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-start" role="alert">
                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                    <div>
                        <strong>Perhatian!</strong>
                        <p class="mb-0 mt-1">Pastikan pengunjung benar-benar telah selesai berkunjung sebelum melakukan checkout.</p>
                    </div>
                </div>

                <div class="p-3 bg-light rounded mb-3">
                    <h6 class="fw-bold mb-3">Detail Pengunjung:</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted d-block">No. Kartu:</small>
                            <strong id="confirmCardNumber">-</strong>
                        </div>
                        <div class="col-12" id="confirmNameContainer" style="display: none;">
                            <small class="text-muted d-block">Nama:</small>
                            <strong id="confirmName">-</strong>
                        </div>
                    </div>
                </div>

                <p class="mb-0 text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Setelah checkout, pengunjung dianggap telah meninggalkan lokasi.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmCheckout()">
                    <i class="fas fa-check me-1"></i>Ya, Checkout Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .table > :not(caption) > * > * {
        padding: 0.75rem;
    }
</style>

<script>
let checkoutModal;

document.addEventListener('DOMContentLoaded', function() {
    checkoutModal = new bootstrap.Modal(document.getElementById('checkoutConfirmModal'));
});

function showCheckoutConfirmation() {
    const cardNumber = document.getElementById('visitor_card_number').value;

    if (!cardNumber) {
        alert('Silakan masukkan nomor kartu pengunjung terlebih dahulu');
        return;
    }

    // Update modal with card number
    document.getElementById('confirmCardNumber').textContent = cardNumber;
    document.getElementById('confirmNameContainer').style.display = 'none';

    // Show modal
    checkoutModal.show();
}

function quickCheckout(cardNumber, visitorName) {
    // Pre-fill the form
    document.getElementById('visitor_card_number').value = cardNumber;

    // Update modal with visitor details
    document.getElementById('confirmCardNumber').textContent = cardNumber;
    document.getElementById('confirmName').textContent = visitorName;
    document.getElementById('confirmNameContainer').style.display = 'block';

    // Show confirmation modal
    checkoutModal.show();
}

function confirmCheckout() {
    // Submit the form
    document.getElementById('checkoutForm').submit();
}
</script>
@endsection