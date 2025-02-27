<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users:list')->only('index');
        $this->middleware('permission:users:view')->only('show');
        $this->middleware('permission:users:create')->only('store');
        $this->middleware('permission:users:edit')->only('update');
        $this->middleware('permission:users:delete')->only('destroy');
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'ILIKE', "%{$searchTerm}%");
            });
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        $users = $query->orderBy($sortField, $sortDirection)
                      ->with('roles')
                      ->paginate($request->per_page ?? 15);
        
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'sometimes|array'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles')
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user->load('roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'sometimes|required|string|min:8',
            'roles' => 'sometimes|array'
        ]);

        if ($request->has('name')) {
            $user->name = $validated['name'];
        }
        
        if ($request->has('email')) {
            $user->email = $validated['email'];
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }
        
        $user->delete();
        
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}