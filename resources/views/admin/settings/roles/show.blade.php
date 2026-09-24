@extends('layouts.admin')

@section('title', 'Detail Role')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}</h1>
                    <p class="text-gray-600">Detail informasi role dan permissions</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Role
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Role Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Info Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Informasi Role</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Nama Role</label>
                            <p class="text-lg font-bold">{{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-500">Identifier</label>
                            <p class="text-sm font-mono bg-base-200 px-3 py-2 rounded">{{ $role->name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-500">Dibuat</label>
                            <p class="text-sm">{{ $role->created_at->format('d M Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-500">Terakhir Diupdate</label>
                            <p class="text-sm">{{ $role->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Statistik</h2>

                    <div class="stats stats-vertical shadow w-full">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <div class="stat-title">Total Permissions</div>
                            <div class="stat-value text-primary">{{ $role->permissions->count() }}</div>
                        </div>

                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="stat-title">Users dengan Role Ini</div>
                            <div class="stat-value text-secondary">{{ $role->users->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions & Users -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Permissions Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Permissions yang Dimiliki</h2>

                    @if($role->permissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $groupedPermissions = $role->permissions->groupBy(function($permission) {
                                    $parts = explode(' ', $permission->name);
                                    return count($parts) > 1 ? $parts[1] : 'other';
                                });
                            @endphp

                            @foreach($groupedPermissions as $module => $permissions)
                            <div class="card bg-base-200">
                                <div class="card-body p-4">
                                    <h3 class="font-bold text-lg capitalize mb-3">{{ str_replace('_', ' ', $module) }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($permissions as $permission)
                                        <div class="badge badge-primary badge-outline">
                                            {{ $permission->name }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>Role ini belum memiliki permission apapun.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Users Card -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Users dengan Role Ini</h2>

                    @if($role->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                    <tr>
                                        <td>
                                            <div class="flex items-center space-x-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral-focus text-neutral-content rounded-full w-10">
                                                        <span class="text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-bold">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <div class="badge badge-ghost">{{ ucfirst($user->user_type) }}</div>
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <div class="badge badge-success">Aktif</div>
                                            @else
                                                <div class="badge badge-error">Nonaktif</div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-ghost">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Belum ada user yang menggunakan role ini.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
