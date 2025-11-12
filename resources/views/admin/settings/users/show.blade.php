@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Detail User</h1>
            <p class="text-gray-600">Informasi lengkap user</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('suadmin.users.edit', $user) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit User
            </a>
            <a href="{{ route('suadmin.users.index') }}" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24">
                            <span class="text-3xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <h2 class="card-title justify-center">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>

                    <!-- Status Badge -->
                    @if($user->is_active)
                        <div class="badge badge-success gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Aktif
                        </div>
                    @else
                        <div class="badge badge-error gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Nonaktif
                        </div>
                    @endif

                    <!-- Roles -->
                    <div class="divider"></div>
                    <div class="text-left">
                        <h3 class="font-semibold mb-2">Role:</h3>
                        <div class="flex flex-wrap gap-2">
                            @forelse($user->roles as $role)
                                <span class="badge badge-primary">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                            @empty
                                <span class="text-gray-500 text-sm">Tidak ada role</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="divider"></div>
                    <div class="card-actions flex-col">
                        @if($user->id !== auth()->id() && !$user->hasRole('super-admin'))
                            <form action="{{ route('suadmin.users.toggle-status', $user) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm w-full">
                                    @if($user->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Nonaktifkan User
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Aktifkan User
                                    @endif
                                </button>
                            </form>

                            <button type="button" onclick="resetPasswordModal.showModal()" class="btn btn-outline btn-warning btn-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Reset Password
                            </button>

                            <button type="button" onclick="deleteUserModal.showModal()" class="btn btn-outline btn-error btn-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus User
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="lg:col-span-2">
            <!-- Basic Information -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Informasi Dasar</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Nama Lengkap</label>
                            <p class="text-lg">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Email</label>
                            <p class="text-lg">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Tipe User</label>
                            <p class="text-lg">{{ ucwords($user->user_type) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Kode Registrasi</label>
                            <p class="text-lg">{{ $user->registration_code ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Email Terverifikasi</label>
                            <p class="text-lg">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Ya</span>
                                    <span class="text-sm text-gray-500">({{ $user->email_verified_at->format('d M Y H:i') }})</span>
                                @else
                                    <span class="badge badge-warning">Belum</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Status</label>
                            <p class="text-lg">
                                @if($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-error">Nonaktif</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Terdaftar Sejak</label>
                            <p class="text-lg">{{ $user->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Terakhir Diupdate</label>
                            <p class="text-lg">{{ $user->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Permissions</h2>
                    @if($user->permissions->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->permissions as $permission)
                                <span class="badge badge-outline">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Tidak ada permission langsung. Permission diatur melalui role.</p>
                    @endif
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Statistik Aktivitas</h2>
                    <div class="stats shadow w-full">
                        <div class="stat">
                            <div class="stat-figure text-primary" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div class="stat-title">Total Ticket</div>
                            <div class="stat-value text-primary">{{ $user->tickets->count() }}</div>
                            <div class="stat-desc">Ticket yang dibuat</div>
                        </div>

                        <div class="stat">
                            <div class="stat-figure text-secondary" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="stat-title">Ticket Ditugaskan</div>
                            <div class="stat-value text-secondary">{{ $user->assignedTickets->count() }}</div>
                            <div class="stat-desc">Ticket yang ditugaskan ke user ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<dialog id="resetPasswordModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reset Password User</h3>
        <form action="{{ route('suadmin.users.reset-password', $user) }}" method="POST">
            @csrf
            <div class="form-control w-full mb-4">
                <label class="label" for="new_password">
                    <span class="label-text font-semibold">Password Baru <span class="text-error">*</span></span>
                </label>
                <input type="password" id="new_password" name="new_password" class="input input-bordered w-full" placeholder="Masukkan password baru" required aria-required="true">
            </div>
            <div class="form-control w-full mb-4">
                <label class="label" for="new_password_confirmation">
                    <span class="label-text font-semibold">Konfirmasi Password Baru <span class="text-error">*</span></span>
                </label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="input input-bordered w-full" placeholder="Konfirmasi password baru" required aria-required="true">
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="resetPasswordModal.close()">Batal</button>
                <button type="submit" class="btn btn-warning">Reset Password</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Delete User Modal -->
<dialog id="deleteUserModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Hapus User</h3>
        <p class="mb-4">Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <form action="{{ route('suadmin.users.destroy', $user) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-action">
                <button type="button" class="btn" onclick="deleteUserModal.close()">Batal</button>
                <button type="submit" class="btn btn-error">Hapus User</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection
