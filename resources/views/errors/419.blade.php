@extends('errors.layout')

@section('title', '419 - Sesi Kedaluwarsa')

@section('content')
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full text-yellow-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    <h1 class="error-code">419</h1>
    <h2 class="error-title">Sesi Kedaluwarsa</h2>
    <p class="error-message">
        Sesi Anda telah kedaluwarsa karena tidak ada aktivitas dalam waktu yang lama. Silakan muat ulang halaman dan coba lagi.
    </p>

    <div>
        <a href="javascript:location.reload()" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Muat Ulang Halaman
        </a>
        <a href="{{ url('/') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>

    <div class="error-details">
        <strong>Tips Keamanan:</strong><br>
        Token CSRF telah kedaluwarsa untuk melindungi data Anda. Ini adalah fitur keamanan normal yang mencegah serangan berbahaya.
    </div>
@endsection
