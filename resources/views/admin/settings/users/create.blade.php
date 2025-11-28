@extends('layouts.admin')

@section('title', 'Tambah User Baru')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('suadmin.users.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Tambah User Baru</h1>
        </div>
        <p class="text-gray-600">Buat akun user baru untuk sistem</p>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
    <div class="alert alert-error shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="font-bold">Terdapat kesalahan!</h3>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('suadmin.users.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Information -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Informasi User</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div class="form-control w-full md:col-span-2">
                                <label class="label">
                                    <span class="label-text font-semibold">Nama Lengkap <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full @error('name') input-error @enderror" placeholder="Masukkan nama lengkap" required>
                                @error('name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Email <span class="text-error">*</span></span>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full @error('email') input-error @enderror" placeholder="user@example.com" required>
                                @error('email')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                                @enderror
                            </div>

                            <!-- Registration Code -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Kode Registrasi</span>
                                </label>
                                <input type="text" name="registration_code" value="{{ old('registration_code') }}" class="input input-bordered w-full" placeholder="Opsional">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Untuk user internal (guru, pegawai, siswa)</span>
                                </label>
                            </div>

                            <!-- Password -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Password <span class="text-error">*</span></span>
                                </label>
                                <input type="password" name="password" class="input input-bordered w-full @error('password') input-error @enderror" placeholder="Minimal 8 karakter" required>
                                @error('password')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                                @enderror
                            </div>

                            <!-- Password Confirmation -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Konfirmasi Password <span class="text-error">*</span></span>
                                </label>
                                <input type="password" name="password_confirmation" class="input input-bordered w-full" placeholder="Ulangi password" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role & Settings -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Role & Type Card -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title mb-4">Role & Tipe User</h2>

                            <!-- User Type -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Tipe User <span class="text-error">*</span></span>
                                </label>
                                <select name="user_type" class="select select-bordered w-full @error('user_type') select-error @enderror" required>
                                    <option value="" disabled selected>Pilih Tipe User</option>
                                    @foreach($userTypes as $key => $value)
                                    <option value="{{ $key }}" {{ old('user_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('user_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                                @enderror
                            </div>

                            <!-- Role -->
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Role <span class="text-error">*</span></span>
                                </label>
                                <select name="role" class="select select-bordered w-full @error('role') select-error @enderror" required>
                                    <option value="" disabled selected>Pilih Role</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="label-text font-semibold">Aktifkan User</span>
                                </label>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">User dapat login jika diaktifkan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card bg-info text-info-content shadow-xl">
                        <div class="card-body">
                            <h3 class="font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Informasi
                            </h3>
                            <div class="text-sm space-y-2 mt-2">
                                <p>• Email akan diverifikasi otomatis</p>
                                <p>• User dapat login langsung setelah dibuat</p>
                                <p>• Password minimal 8 karakter</p>
                                <p>• Role menentukan hak akses user</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ route('suadmin.users.index') }}" class="btn btn-ghost">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan User
            </button>
        </div>
    </form>
</div>
@endsection
