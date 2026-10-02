@extends('layouts.public')

@section('title', 'Pertanyaan Umum (FAQ) - ' . app_brand_name())

@section('content')
<div class="container py-5" style="max-width: 860px;">
    <div class="text-center mb-5">
        <h1 class="display-6 fw-bold mb-3">Pertanyaan Umum (FAQ)</h1>
        <p class="lead text-muted mb-0">Jawaban atas pertanyaan yang sering diajukan tentang layanan PTSP.</p>
    </div>

    @include('public.partials.faq-list', ['faqs' => $faqs, 'searchable' => true])

    <div class="text-center mt-5">
        <p class="text-muted mb-2">Belum menemukan jawabannya?</p>
        <a href="{{ route('public.contact') }}" class="btn btn-outline-primary">
            <i class="fas fa-envelope me-2" aria-hidden="true"></i>Hubungi Kami
        </a>
    </div>
</div>
@endsection
