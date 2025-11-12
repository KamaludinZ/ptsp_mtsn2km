<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function($permission) {
            // Group permissions by prefix (e.g., 'view', 'create', 'edit', 'delete')
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        return view('admin.settings.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            // Group permissions by module
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        return view('admin.settings.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'array'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            return redirect()->route('suadmin.roles.index')
                ->with('success', 'Role berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        return view('admin.settings.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.settings.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        try {
            DB::beginTransaction();

            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return redirect()->route('suadmin.roles.index')
                ->with('success', 'Role berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        try {
            // Prevent deleting super-admin role
            if ($role->name === 'super-admin') {
                return redirect()->back()
                    ->with('error', 'Role Super Admin tidak dapat dihapus!');
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Role tidak dapat dihapus karena masih memiliki ' . $role->users()->count() . ' user!');
            }

            $role->delete();

            return redirect()->route('suadmin.roles.index')
                ->with('success', 'Role berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }

    /**
     * Assign permissions to role (AJAX)
     */
    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        try {
            $role->syncPermissions($request->permissions);

            return response()->json([
                'success' => true,
                'message' => 'Permissions berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role permissions (AJAX)
     */
    public function getPermissions(Role $role)
    {
        return response()->json([
            'role' => $role->name,
            'permissions' => $role->permissions->pluck('name')
        ]);
    }

    /**
     * Clone/duplicate a role
     */
    public function clone(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255'
        ]);

        try {
            DB::beginTransaction();

            $newRole = Role::create(['name' => $request->name]);
            $newRole->syncPermissions($role->permissions);

            DB::commit();

            return redirect()->route('suadmin.roles.index')
                ->with('success', 'Role berhasil diduplikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menduplikasi role: ' . $e->getMessage());
        }
    }
}
