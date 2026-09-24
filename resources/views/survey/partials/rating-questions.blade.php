{{-- Radio-card list for SKM/SPAK questions managed from the admin survey menu. --}}
@forelse ($questions as $question)
    @php($field = 'answers.' . $question->id)
    <fieldset class="question-card mb-4 p-4 border rounded bg-light">
        <legend class="h6 fw-semibold mb-3 float-none w-auto">
            <span class="badge bg-{{ $accent }} me-2">{{ $loop->iteration }}</span>
            {{ $question->question }}
            @if ($question->is_required)
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </legend>

        <div class="row g-2">
            @foreach ((array) $question->options as $option)
                @php($inputId = 'q' . $question->id . '_' . $loop->iteration)
                <div class="col-md-6">
                    <div class="form-check form-check-card ps-0">
                        <input class="form-check-input visually-hidden @error($field) is-invalid @enderror"
                               type="radio"
                               name="answers[{{ $question->id }}]"
                               id="{{ $inputId }}"
                               value="{{ $option }}"
                               @checked(old($field) === $option)
                               @required($question->is_required)>
                        <label class="form-check-label w-100 p-3 border rounded" for="{{ $inputId }}">
                            {{ $option }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        @error($field)
            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
        @enderror
    </fieldset>
@empty
    <div class="alert alert-warning">
        Pertanyaan survei belum tersedia. Silakan hubungi petugas PTSP.
    </div>
@endforelse

@once
    @push('styles')
        <style>
            .form-check-card .form-check-label { cursor: pointer; background: #fff; }
            .form-check-card .form-check-input:focus-visible + .form-check-label {
                outline: 3px solid #1d4ed8;
                outline-offset: 2px;
            }
            .form-check-card .form-check-input:checked + .form-check-label {
                background: #dbeafe;
                border-color: #1d4ed8 !important;
                font-weight: 600;
            }
        </style>
    @endpush
@endonce
