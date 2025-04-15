<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Alumni\StoreAlumniRequest;
use App\Http\Requests\Alumni\UpdateAlumniRequest;
use App\Http\Resources\AlumniResource;
use App\Models\ActivityLog;
use App\Models\Alumni;
use App\Models\EducationHistory;
use App\Models\WorkExperience;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\Authorize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumniController extends Controller
{
    #[Authorize('permission:alumni:list')]
    public function index(Request $request)
    {
        $query = Alumni::query();
        
        // Implement search and filtering
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'ILIKE', "%{$searchTerm}%");
            });
        }
        
        if ($request->has('faculty')) {
            $query->where('faculty', $request->faculty);
        }
        
        if ($request->has('degree')) {
            $query->where('degree', $request->degree);
        }
        
        if ($request->has('graduation_year')) {
            $query->whereYear('graduation_date', $request->graduation_year);
        }
        
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }
        
        // Sorting
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $alumni = $query->paginate($perPage);
        
        return AlumniResource::collection($alumni);
    }

    #[Authorize('permission:alumni:create')]
    public function store(StoreAlumniRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('alumni/photos', 'public');
                $data['profile_photo'] = $path;
            }
            
            // Create alumni record
            $alumni = Alumni::create($data);
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'entity_type' => 'alumni',
                'entity_id' => $alumni->id,
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            DB::commit();
            
            return new AlumniResource($alumni);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating alumni record', 'error' => $e->getMessage()], 500);
        }
    }

    #[Authorize('permission:alumni:view')]
    public function show($id)
    {   
        $alumni = Alumni::findOrFail($id);
        return new AlumniResource($alumni);
    }

    #[Authorize('permission:alumni:edit')]
    public function update(UpdateAlumniRequest $request, $id)
    {
        $alumni = Alumni::findOrFail($id);
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            $oldValues = $alumni->toArray();
            
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($alumni->profile_photo) {
                    Storage::disk('public')->delete($alumni->profile_photo);
                }
                
                $path = $request->file('profile_photo')->store('alumni/photos', 'public');
                $data['profile_photo'] = $path;
            }
            
            // Update alumni record
            $alumni->update($data);
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'entity_type' => 'alumni',
                'entity_id' => $alumni->id,
                'old_values' => $oldValues,
                'new_values' => $alumni->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            DB::commit();
            
            return new AlumniResource($alumni);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating alumni record', 'error' => $e->getMessage()], 500);
        }
    }

    #[Authorize('permission:alumni:delete')]
    public function destroy(Request $request, $id)
    {
        $alumni = Alumni::findOrFail($id);
        try {
            $oldValues = $alumni->toArray();
            
            // Delete profile photo if exists
            if ($alumni->profile_photo) {
                Storage::disk('public')->delete($alumni->profile_photo);
            }
            
            // Log activity before deletion
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'entity_type' => 'alumni',
                'entity_id' => $alumni->id,
                'old_values' => $oldValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Soft delete the alumni record
            $alumni->delete();
            
            return response()->json(['message' => 'Alumni record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting alumni record', 'error' => $e->getMessage()], 500);
        }
    }
}