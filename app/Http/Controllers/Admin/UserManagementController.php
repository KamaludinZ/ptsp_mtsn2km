<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->role($request->role);
        }

        // Filter by user type
        if ($request->has('user_type') && $request->user_type != '') {
            $query->where('user_type', $request->user_type);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();

        // User types
        $userTypes = [
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Umum'
        ];

        return view('admin.settings.users.index', compact('users', 'roles', 'userTypes'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();

        $userTypes = [
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Umum'
        ];

        return view('admin.settings.users.create', compact('roles', 'userTypes'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|in:guru,pegawai,siswa,walimurid,alumni,instansi,umum',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'registration_code' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'is_active' => $request->has('is_active') ? true : false,
                'registration_code' => $request->registration_code,
                'email_verified_at' => now(), // Auto verify for admin-created users
            ]);

            $user->assignRole($request->role);

            DB::commit();

            return redirect()->route('suadmin.users.index')
                ->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions', 'tickets', 'assignedTickets');

        return view('admin.settings.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();

        $userTypes = [
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Umum'
        ];

        $userRoles = $user->roles->pluck('name')->toArray();

        return view('admin.settings.users.edit', compact('user', 'roles', 'userTypes', 'userRoles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|in:guru,pegawai,siswa,walimurid,alumni,instansi,umum',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'registration_code' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'user_type' => $request->user_type,
                'is_active' => $request->has('is_active') ? true : false,
                'registration_code' => $request->registration_code,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Update role
            $user->syncRoles([$request->role]);

            DB::commit();

            return redirect()->route('suadmin.users.index')
                ->with('success', 'User berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting current user
            if ($user->id === auth()->id()) {
                return redirect()->back()
                    ->with('error', 'Anda tidak dapat menghapus akun sendiri!');
            }

            // Prevent deleting admin
            if ($user->hasAnyRole(['admin', 'super_admin'])) {
                return redirect()->back()
                    ->with('error', 'Admin tidak dapat dihapus!');
            }

            $user->delete();

            return redirect()->route('suadmin.users.index')
                ->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        try {
            // Prevent deactivating admin
            if ($user->hasAnyRole(['admin', 'super_admin'])) {
                return redirect()->back()
                    ->with('error', 'Status Admin tidak dapat diubah!');
            }

            // Prevent deactivating current user
            if ($user->id === auth()->id()) {
                return redirect()->back()
                    ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri!');
            }

            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->back()
                ->with('success', "User berhasil {$status}!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return redirect()->back()
                ->with('success', 'Password user berhasil direset!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    /**
     * Bulk action for users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $users = User::whereIn('id', $request->user_ids)->get();

            foreach ($users as $user) {
                // Skip current user and admin
                if ($user->id === auth()->id() || $user->hasAnyRole(['admin', 'super_admin'])) {
                    continue;
                }

                switch ($request->action) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        break;
                    case 'deactivate':
                        $user->update(['is_active' => false]);
                        break;
                    case 'delete':
                        $user->delete();
                        break;
                }
            }

            return redirect()->back()
                ->with('success', 'Bulk action berhasil dijalankan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menjalankan bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality (Excel/CSV)
        return redirect()->back()
            ->with('info', 'Fitur export sedang dalam pengembangan.');
    }
}
