@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat - Identitas Responden')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Survey Kepuasan Masyarakat
                </h2>
                @include('survey.partials.edition')
            </div>

            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tahap 1 dari 3</span>
                    <span class="text-muted small">33%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 33%;"
                         aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Tahap 1: Identitas Responden
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Silakan lengkapi data diri Anda terlebih dahulu. Semua informasi akan dijaga kerahasiaannya.
                    </p>

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

                    <form action="{{ route('survey.step1.store') }}" method="POST" id="step1Form">
                        @csrf

                        <p class="small text-muted mb-4"><span class="text-danger fw-bold">*</span> wajib diisi</p>

                        @foreach ($identityQuestions as $question)
                            @php
                                $field = 'answers.' . $question->id;
                                $name = 'answers[' . $question->id . ']';
                                $id = 'q' . $question->id;
                                $options = $question->field_type === 'select' && stripos($question->question, 'jenis pelayanan') !== false && empty($question->options)
                                    ? \App\Models\Service::orderBy('name')->pluck('name')->all()
                                    : (array) $question->options;
                                $isTicket = stripos($question->question, 'tiket') !== false;
                                $inputType = match (true) {
                                    $question->field_type === 'email' => 'email',
                                    $question->field_type === 'tel' => 'tel',
                                    $question->field_type === 'number' => 'number',
                                    default => 'text',
                                };
                                $required = (bool) $question->is_required;
                            @endphp
                            <div class="mb-4">
                                <label class="form-label fw-semibold" @if($question->field_type !== 'radio') for="{{ $id }}" @endif>
                                    {{ $question->question }}
                                    @if ($required)
                                        <span class="text-danger" title="Wajib diisi" aria-hidden="true">*</span>
                                        <span class="visually-hidden">(wajib diisi)</span>
                                    @else
                                        <span class="text-muted fw-normal small">(opsional)</span>
                                    @endif
                                </label>

                                @if ($question->field_type === 'select')
                                    <select id="{{ $id }}" name="{{ $name }}" class="form-select @error($field) is-invalid @enderror" @required($required)>
                                        <option value="">-- {{ \Illuminate\Support\Str::startsWith($question->question, 'Pilih') ? $question->question : 'Pilih ' . $question->question }} --</option>
                                        @foreach ($options as $option)
                                            <option value="{{ $option }}" @selected(old($field) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($question->field_type === 'radio')
                                    <div class="row g-2" role="radiogroup" aria-label="{{ $question->question }}">
                                        @foreach ($options as $option)
                                            <div class="col-sm-6">
                                                <div class="form-check-card">
                                                    <input class="form-check-input visually-hidden @error($field) is-invalid @enderror" type="radio"
                                                           name="{{ $name }}" id="{{ $id }}_{{ $loop->index }}" value="{{ $option }}"
                                                           @checked(old($field) === $option) @required($required)>
                                                    <label class="form-check-label w-100 p-2 px-3 border rounded" for="{{ $id }}_{{ $loop->index }}">{{ $option }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($isTicket)
                                    <div class="input-group">
                                        <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ old($field, request()->query('tiket')) }}"
                                               class="form-control @error($field) is-invalid @enderror"
                                               placeholder="Contoh: LAYANAN-N-2025-001" autocomplete="off" @required($required)>
                                        <button class="btn btn-outline-secondary" type="button" id="checkTicketBtn" data-input="{{ $id }}">
                                            <i class="fas fa-search me-1" aria-hidden="true"></i>Cek
                                        </button>
                                    </div>
                                    <div id="ticketInfo" class="mt-2" aria-live="polite"></div>
                                @else
                                    <input type="{{ $inputType }}" id="{{ $id }}" name="{{ $name }}" value="{{ old($field) }}"
                                           class="form-control @error($field) is-invalid @enderror"
                                           @if($question->field_type === 'tel') inputmode="tel" pattern="[0-9+\-\s()]{8,20}" placeholder="08xxxxxxxxxx" @endif
                                           @if($question->field_type === 'email') placeholder="nama@contoh.com" @endif
                                           @required($required)>
                                @endif

                                @error($field)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Selanjutnya<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Data Anda aman dan akan dijaga kerahasiaannya
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
        border-bottom: 3px solid rgba(255, 255, 255, 0.2);
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        padding: 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }
    .form-check-card .form-check-label { cursor: pointer; background: #fff; transition: all .15s ease; }
    .form-check-card .form-check-label:hover { border-color: #166534 !important; background: #f0fdf4; }
    .form-check-card .form-check-input:focus-visible + .form-check-label { outline: 3px solid #ea580c; outline-offset: 2px; }
    .form-check-card .form-check-input:checked + .form-check-label { background: #dcfce7; border-color: #166534 !important; font-weight: 600; color: #14532d; }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
@include('survey.partials.draft', ['form' => 'step1Form'])
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('checkTicketBtn');
    if (!btn) return;
    var info = document.getElementById('ticketInfo');
    function show(type, icon, text) {
        info.innerHTML = '<div class="alert alert-' + type + ' py-2 mb-0"><i class="fas ' + icon + ' me-2"></i></div>';
        info.firstChild.appendChild(document.createTextNode(text));
    }
    btn.addEventListener('click', function () {
        var value = document.getElementById(btn.dataset.input).value.trim();
        if (!value) { show('warning', 'fa-exclamation-triangle', 'Isi kode tiket terlebih dahulu'); return; }
        fetch('/api/check-ticket/' + encodeURIComponent(value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.exists) return show('warning', 'fa-exclamation-triangle', 'Tiket tidak ditemukan');
                if (data.has_survey_completed) return show('warning', 'fa-exclamation-triangle', 'Tiket ini sudah menyelesaikan survei');
                show('success', 'fa-check-circle', 'Tiket ditemukan');
            })
            .catch(function () { show('danger', 'fa-exclamation-triangle', 'Gagal memeriksa tiket'); });
    });
});
</script>
@endpush
