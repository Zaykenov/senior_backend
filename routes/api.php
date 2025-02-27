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
    
    // Alumni routes
    Route::apiResource('alumni', AlumniController::class);
    
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
