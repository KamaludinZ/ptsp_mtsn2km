@extends('errors.layout')

@section('title', 'IP Diblokir')

@section('content')
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full text-red-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
    </div>

    <h1 class="error-code">🚫</h1>
    <h2 class="error-title">Akses Diblokir</h2>
    <p class="error-message">
        Alamat IP Anda telah diblokir dari mengakses sistem ini.
    </p>

    <div class="error-details">
        <strong>Alasan:</strong> {{ $reason ?? 'Aktivitas mencurigakan terdeteksi' }}<br>
        @if($expires_at)
            <strong>Blokir akan berakhir:</strong> {{ \Carbon\Carbon::parse($expires_at)->format('d M Y H:i') }}<br>
            <strong>Sisa waktu:</strong> {{ \Carbon\Carbon::parse($expires_at)->diffForHumans() }}
        @else
            <strong>Jenis blokir:</strong> Permanen
        @endif
    </div>

    <div class="error-details" style="margin-top: 1.5rem;">
        <strong>Jika Anda merasa ini adalah kesalahan:</strong><br>
        Silakan hubungi administrator sistem dengan menyertakan informasi berikut:<br>
        <strong>IP Address:</strong> {{ request()->ip() }}<br>
        <strong>Waktu:</strong> {{ now()->format('d M Y H:i:s') }}<br>
        <strong>User Agent:</strong> {{ request()->userAgent() }}
    </div>
@endsection
