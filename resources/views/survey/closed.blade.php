@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3.5rem; opacity: 0.4;" aria-hidden="true"></i>
            <h2 class="fw-bold">Survei belum dibuka</h2>
            <p class="text-muted mb-4">
                Saat ini belum ada edisi Survey Kepuasan Masyarakat yang aktif. Silakan kembali lagi nanti.
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="fas fa-home me-2" aria-hidden="true"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
