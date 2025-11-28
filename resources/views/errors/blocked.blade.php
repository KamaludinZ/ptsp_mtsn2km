@extends('errors.layout')

@section('title', __('IP Blocked'))
@section('code', '🚫')
@section('message')
    <p class="error-message">
        Alamat IP Anda ({{ request()->ip() }}) telah diblokir dari mengakses sistem ini.
    </p>

    <div class="error-details" style="margin-top: 1.5rem; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto;">
        <h3 style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">Detail Blokir:</h3>
        <p style="margin-bottom: 0.5rem;"><strong>Alasan:</strong> {{ $reason ?? 'Aktivitas mencurigakan terdeteksi' }}</p>
        @if(isset($expires_at) && $expires_at)
            <p style="margin-bottom: 0.5rem;"><strong>Blokir berakhir:</strong> {{ \Carbon\Carbon::parse($expires_at)->format('d M Y H:i') }} ({{ \Carbon\Carbon::parse($expires_at)->diffForHumans() }})</p>
        @else
            <p style="margin-bottom: 0.5rem;"><strong>Jenis blokir:</strong> Permanen</p>
        @endif
    </div>

    <div class="error-details" style="margin-top: 1.5rem; text-align: left; max-width: 450px; margin-left: auto; margin-right: auto; background: #fffbe6; border-left-color: #f59e0b;">
        <h3 style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">Merasa ini kesalahan?</h3>
        <p>Silakan hubungi administrator sistem dengan menyertakan informasi di bawah ini:</p>
        <ul style="font-size: 0.875rem; padding-left: 1.25rem; margin-top: 0.5rem;">
            <li><strong>IP Address:</strong> {{ request()->ip() }}</li>
            <li><strong>Waktu:</strong> {{ now()->format('d M Y H:i:s') }}</li>
        </ul>
    </div>
@endsection
