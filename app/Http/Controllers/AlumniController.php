<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlumniController extends Controller
{
    /**
     * Display a listing of the alumni.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $alumnis = Alumni::all(); // For now, get all. We'll add pagination and filtering later.
        return response()->json(['alumnis' => $alumnis]);
    }

    /**
     * Store a newly created alumni in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([ // Basic validation, expand as needed
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:alumnis',
            // Add validations for other fields
        ]);

        $alumni = Alumni::create($request->all());
        return response()->json(['alumni' => $alumni, 'message' => 'Alumni created successfully'], 201); // 201 Created status
    }

    /**
     * Display the specified alumni.
     *
     * @param  \App\Models\Alumni  $alumni
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Alumni $alumni): JsonResponse
    {
        return response()->json(['alumni' => $alumni]);
    }

    /**
     * Update the specified alumni in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Alumni  $alumni
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Alumni $alumni): JsonResponse
    {
        $request->validate([ // Validation for updates, adjust as needed
            'name' => 'string|max:255',
            'email' => 'email|unique:alumnis,email,' . $alumni->id, // Ignore current email
            // Add validations for other fields
        ]);

        $alumni->update($request->all());
        return response()->json(['alumni' => $alumni, 'message' => 'Alumni updated successfully']);
    }

    /**
     * Remove the specified alumni from storage.
     *
     * @param  \App\Models\Alumni  $alumni
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Alumni $alumni): JsonResponse
    {
        $alumni->delete();
        return response()->json(['message' => 'Alumni deleted successfully']);
    }
}