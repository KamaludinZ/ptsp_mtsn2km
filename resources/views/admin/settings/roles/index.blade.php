@extends('layouts.admin')

@section('title', 'Manajemen Role & Hak Akses')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Manajemen Role & Hak Akses</h1>
            <p class="text-gray-600">Kelola role dan permissions untuk kontrol akses sistem</p>
        </div>
        <a href="{{ route('suadmin.roles.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Role Baru
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-title">Total Roles</div>
            <div class="stat-value text-primary">{{ $roles->count() }}</div>
            <div class="stat-desc">Role aktif dalam sistem</div>
        </div>

        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-figure text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div class="stat-title">Total Permissions</div>
            <div class="stat-value text-secondary">{{ $permissions->flatten()->count() }}</div>
            <div class="stat-desc">Permission terdaftar</div>
        </div>

        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-figure text-accent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-title">Kategori Permission</div>
            <div class="stat-value text-accent">{{ $permissions->count() }}</div>
            <div class="stat-desc">Grup permission</div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-4">Daftar Role</h2>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Jumlah Permissions</th>
                            <th>Jumlah Users</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral-focus text-neutral-content rounded-full w-12">
                                            <span class="text-xl">{{ strtoupper(substr($role->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}</div>
                                        <div class="text-sm opacity-50">{{ $role->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-primary">{{ $role->permissions->count() }} permissions</div>
                            </td>
                            <td>
                                <div class="badge badge-ghost">{{ $role->users->count() }} users</div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('suadmin.roles.show', $role) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('suadmin.roles.edit', $role) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @if($role->name !== 'admin')
                                    <form action="{{ route('suadmin.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8">
                                <div class="text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p>Belum ada role yang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Permissions Reference -->
    <div class="card bg-base-100 shadow-xl mt-6">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-4">Referensi Permissions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($permissions as $module => $modulePermissions)
                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-lg capitalize">{{ str_replace('_', ' ', $module) }}</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($modulePermissions as $permission)
                            <div class="badge badge-outline">{{ $permission->name }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
