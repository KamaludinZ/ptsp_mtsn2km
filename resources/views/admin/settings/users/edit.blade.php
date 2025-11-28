@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit User</h1>
            <p class="text-gray-600">Perbarui informasi user</p>
        </div>
        <a href="{{ route('suadmin.users.index') }}" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-error shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Form -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form action="{{ route('suadmin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="form-control w-full">
                        <label class="label" for="name">
                            <span class="label-text font-semibold">Nama Lengkap <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="input input-bordered w-full @error('name') input-error @enderror" placeholder="Masukkan nama lengkap" required aria-required="true" aria-describedby="name-error">
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error" id="name-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-control w-full">
                        <label class="label" for="email">
                            <span class="label-text font-semibold">Email <span class="text-error">*</span></span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="input input-bordered w-full @error('email') input-error @enderror" placeholder="Masukkan email" required aria-required="true" aria-describedby="email-error">
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error" id="email-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- User Type -->
                    <div class="form-control w-full">
                        <label class="label" for="user_type">
                            <span class="label-text font-semibold">Tipe User <span class="text-error">*</span></span>
                        </label>
                        <select id="user_type" name="user_type" class="select select-bordered w-full @error('user_type') select-error @enderror" required aria-required="true" aria-describedby="user_type-error">
                            <option value="">Pilih tipe user</option>
                            @foreach($userTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('user_type', $user->user_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('user_type')
                            <label class="label">
                                <span class="label-text-alt text-error" id="user_type-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="form-control w-full">
                        <label class="label" for="role">
                            <span class="label-text font-semibold">Role <span class="text-error">*</span></span>
                        </label>
                        <select id="role" name="role" class="select select-bordered w-full @error('role') select-error @enderror" required aria-required="true" aria-describedby="role-error">
                            <option value="">Pilih role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ in_array($role->name, $userRoles) ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $role->name)) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <label class="label">
                                <span class="label-text-alt text-error" id="role-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Registration Code -->
                    <div class="form-control w-full">
                        <label class="label" for="registration_code">
                            <span class="label-text font-semibold">Kode Registrasi</span>
                        </label>
                        <input type="text" id="registration_code" name="registration_code" value="{{ old('registration_code', $user->registration_code) }}" class="input input-bordered w-full" placeholder="Masukkan kode registrasi (opsional)" aria-describedby="registration_code-help">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="registration_code-help">Kode registrasi untuk validasi (opsional)</span>
                        </label>
                    </div>

                    <!-- Status -->
                    <div class="form-control w-full">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" {{ old('is_active', $user->is_active) ? 'checked' : '' }} aria-describedby="is_active-help">
                            <span class="label-text font-semibold">User Aktif</span>
                        </label>
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="is_active-help">Centang untuk mengaktifkan user</span>
                        </label>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="divider"></div>
                <h3 class="text-xl font-semibold mb-4">Ubah Password (Opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div class="form-control w-full">
                        <label class="label" for="password">
                            <span class="label-text font-semibold">Password Baru</span>
                        </label>
                        <input type="password" id="password" name="password" class="input input-bordered w-full @error('password') input-error @enderror" placeholder="Masukkan password baru" aria-describedby="password-help password-error">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="password-help">Kosongkan jika tidak ingin mengubah password</span>
                        </label>
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error" id="password-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-control w-full">
                        <label class="label" for="password_confirmation">
                            <span class="label-text font-semibold">Konfirmasi Password Baru</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full" placeholder="Konfirmasi password baru" aria-describedby="password_confirmation-help">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="password_confirmation-help">Masukkan ulang password baru</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('suadmin.users.index') }}" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
