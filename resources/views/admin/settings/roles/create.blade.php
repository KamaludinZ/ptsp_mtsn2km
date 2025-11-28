@extends('layouts.admin')

@section('title', 'Tambah Role Baru')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('suadmin.roles.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Tambah Role Baru</h1>
        </div>
        <p class="text-gray-600">Buat role baru dan atur permissions</p>
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

    <form action="{{ route('suadmin.roles.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Info -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Informasi Role</h2>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Nama Role <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full @error('name') input-error @enderror" placeholder="contoh: customer-service" required>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Gunakan huruf kecil dan tanda hubung (-)</span>
                            </label>
                            @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>

                        <div class="alert alert-info mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="text-sm">
                                <p class="font-bold">Tips:</p>
                                <ul class="list-disc list-inside text-xs mt-1">
                                    <li>Pilih nama yang deskriptif</li>
                                    <li>Gunakan format: departemen-jabatan</li>
                                    <li>Contoh: admin, petugas-tu, kepala-sekolah</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="card-title">Permissions</h2>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllPermissions()" class="btn btn-sm btn-outline">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="deselectAllPermissions()" class="btn btn-sm btn-outline">
                                    Hapus Semua
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($permissions as $module => $modulePermissions)
                            <div class="card bg-base-200">
                                <div class="card-body p-4">
                                    <h3 class="font-bold text-lg capitalize mb-3 flex items-center justify-between">
                                        <span>{{ str_replace('_', ' ', $module) }}</span>
                                        <div class="form-control">
                                            <label class="label cursor-pointer gap-2">
                                                <span class="label-text text-xs">Semua</span>
                                                <input type="checkbox" class="checkbox checkbox-sm module-checkbox" data-module="{{ $module }}" onchange="toggleModule(this, '{{ $module }}')">
                                            </label>
                                        </div>
                                    </h3>
                                    <div class="space-y-2">
                                        @foreach($modulePermissions as $permission)
                                        <div class="form-control">
                                            <label class="label cursor-pointer justify-start gap-3">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="checkbox checkbox-primary checkbox-sm permission-checkbox module-{{ $module }}" {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                <span class="label-text">{{ $permission->name }}</span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($permissions->count() === 0)
                        <div class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>Belum ada permission yang tersedia. Silakan jalankan seeder terlebih dahulu.</span>
                        </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-actions justify-end px-6 pb-6">
                        <a href="{{ route('suadmin.roles.index') }}" class="btn btn-ghost">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.querySelectorAll('.module-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.module-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    function toggleModule(checkbox, module) {
        const moduleCheckboxes = document.querySelectorAll(`.module-${module}`);
        moduleCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    // Update module checkbox when individual permissions change
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const moduleClass = Array.from(this.classList).find(c => c.startsWith('module-'));
                if (moduleClass) {
                    const module = moduleClass.replace('module-', '');
                    const moduleCheckboxes = document.querySelectorAll(`.${moduleClass}`);
                    const moduleCheckbox = document.querySelector(`[data-module="${module}"]`);

                    if (moduleCheckbox) {
                        const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                        moduleCheckbox.checked = allChecked;
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection
