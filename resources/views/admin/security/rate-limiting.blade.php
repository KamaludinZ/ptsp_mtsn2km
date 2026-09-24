@extends('layouts.admin')

@section('title', 'Rate Limiting')

@section('content')
<div class="container-fluid p-4">
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Konfigurasi Rate Limiting</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.security.dashboard') }}">Security</a></li>
                    <li>Rate Limiting</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form action="{{ route('admin.security.rate-limiting.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="api_limit" class="label"><span class="label-text">Batas API (per menit)</span></label>
                        <input type="number" name="api_limit" id="api_limit" value="{{ $config['api_limit'] }}" class="input input-bordered w-full">
                    </div>
                    <div>
                        <label for="login_limit" class="label"><span class="label-text">Batas Percobaan Login</span></label>
                        <input type="number" name="login_limit" id="login_limit" value="{{ $config['login_limit'] }}" class="input input-bordered w-full">
                    </div>
                    <div>
                        <label for="general_limit" class="label"><span class="label-text">Batas Umum (per menit)</span></label>
                        <input type="number" name="general_limit" id="general_limit" value="{{ $config['general_limit'] }}" class="input input-bordered w-full">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
