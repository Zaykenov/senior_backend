<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\Authorize;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    #[Authorize('permission:role:list')]
    public function index(Request $request)
    {
        $query = Role::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'ILIKE', "%{$searchTerm}%");
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        $roles = $query->orderBy($sortField, $sortDirection)
                      ->with('permissions')
                      ->paginate($request->per_page ?? 15);
        
        return response()->json($roles);
    }

    /**
     * Store a newly created role.
     */
    #[Authorize('permission:role:create')]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating role', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified role.
     */
    #[Authorize('permission:role:view')]
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    /**
     * Update the specified role.
     */
    #[Authorize('permission:role:edit')]
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id)
            ],
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        // Prevent updates to super-admin role
        if ($role->name === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Only super admins can modify the super-admin role'
            ], 403);
        }

        DB::beginTransaction();
        try {
            if ($request->has('name')) {
                $role->name = $validated['name'];
                $role->save();
            }
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating role', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified role.
     */
    #[Authorize('permission:role:delete')]
    public function destroy(Role $role)
    {
        // Prevent deletion of essential roles
        if (in_array($role->name, ['super-admin', 'admin', 'editor', 'viewer'])) {
            return response()->json([
                'message' => 'This role cannot be deleted as it is essential for system operation'
            ], 403);
        }
        
        try {
            $role->delete();
            
            return response()->json([
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}