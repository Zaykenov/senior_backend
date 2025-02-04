<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    // GET /api/alumni?search=John&graduation_year=2020
    public function index(Request $request)
    {
        $query = Alumni::query();

        // Filter by graduation year
        if ($request->has('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        // Search by name
        if ($request->has('name')) {
            $searchTerm = $request->name;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name->en', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('name->kk', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('name->ru', 'ILIKE', "%{$searchTerm}%");
            });
        }

        return $query->paginate(10);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string',
            'email'           => 'required|email|unique:alumni,email',
            'graduation_year' => 'required|integer'
        ]);

        $alumnus = Alumnus::create($validated);

        return response()->json([
            'message' => __('messages.created'),
            'data'    => $alumnus
        ], 201);
    }

    public function show($id)
    {
        $alumnus = Alumnus::findOrFail($id);
        return response()->json($alumnus);
    }

    public function update(Request $request, $id)
    {
        $alumnus = Alumnus::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|required|string',
            'email'           => 'sometimes|required|email|unique:alumni,email,' . $alumnus->id,
            'graduation_year' => 'sometimes|required|integer'
        ]);

        $alumnus->update($validated);

        return response()->json([
            'message' => __('messages.updated'),
            'data'    => $alumnus
        ]);
    }

    public function destroy($id)
    {
        $alumnus = Alumnus::findOrFail($id);
        $alumnus->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }
}
