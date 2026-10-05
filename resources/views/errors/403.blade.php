@extends('errors.layout')

@php
    $user = auth()->user();
    // Laravel's default "This action is unauthorized." means nothing to a visitor.
    $reason = $exception->getMessage();
    $reason = $reason && ! in_array($reason, ['This action is unauthorized.', 'Forbidden', 'User does not have the right roles.'], true) ? $reason : null;
@endphp

@section('title', 'Akses ditolak')
@section('code', '403')
@section('icon', '<i class="fa-solid fa-lock"></i>')
@section('message')
    @if ($reason)
        {{ $reason }}
    @elseif ($user)
        Akun Anda ({{ $user->name }} · {{ \App\Support\RoleAccess::userRoleLabel($user) }}) tidak memiliki izin untuk membuka halaman ini.
        Bila Anda memerlukan akses, hubungi administrator PTSP.
    @else
        Halaman ini hanya untuk pengguna yang sudah masuk dengan izin yang sesuai.
    @endif
@endsection

@section('actions')
    <div style="display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; margin-bottom: 1rem;">
        @if ($user)
            <a href="{{ url(get_dashboard_route_for_user($user)) }}" class="btn-back">
                <i class="fa-solid fa-gauge-high mr-2" aria-hidden="true"></i> Ke dasbor saya
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-back" style="border: 0; cursor: pointer;">
                    <i class="fa-solid fa-right-left mr-2" aria-hidden="true"></i> Masuk dengan akun lain
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-back">
                <i class="fa-solid fa-right-to-bracket mr-2" aria-hidden="true"></i> Masuk
            </a>
        @endif
    </div>
@endsection
