<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AlumniController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SystemController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']); // Add this line
    
    // Alumni routes - explicit definition with {id} parameter
    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('alumni.store');
    Route::get('/alumni/{id}', [AlumniController::class, 'show'])->name('alumni.show');
    Route::put('/alumni/{id}', [AlumniController::class, 'update'])->name('alumni.update');
    Route::delete('/alumni/{id}', [AlumniController::class, 'destroy'])->name('alumni.destroy');
    
    // User management routes (admin only)
    Route::apiResource('users', UserController::class);
    
    // Role management routes (admin only)
    Route::apiResource('roles', RoleController::class);
    
    // System routes
    Route::prefix('system')->group(function () {
        Route::get('/activity-logs', [SystemController::class, 'activityLogs'])
            ->middleware('permission:system:view-logs');
        Route::get('/statistics', [SystemController::class, 'statistics'])
            ->middleware('permission:system:view-logs');
    });
});
