<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AlumniController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SystemController;
use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast; // Import Broadcast facade

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
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

    // Broadcasting authentication route
    Broadcast::routes(['middleware' => ['auth:sanctum']]); // Add this line

    // Chat routes (These might need adjustment depending on your exact needs, but keep them within auth:sanctum)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users', function (Request $request) {
        // Exclude the current user and potentially filter further if needed
        return User::whereNot('id', $request->user()->id)->get();
    });

    Route::get('/users/{user}', function (User $user) {
        // Ensure the user exists before returning
        return $user;
    });

    Route::get('/messages/{user}', function (User $user, Request $request) {
        return Message::query()
        ->where(function ($query) use ($user, $request) {
            $query->where('sender_id', $request->user()->id)
                ->where('receiver_id', $user->id);
        })
        ->orWhere(function ($query) use ($user, $request) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $request->user()->id);
        })
       ->with(['sender', 'receiver'])
       ->orderBy('id', 'asc')
       ->get();
    });

    Route::post('/messages/{user}', function (User $user, Request $request) {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'text' => $request->message
        ]);

        // Ensure the event is broadcast on the correct private channel
        broadcast(new MessageSent($message))->toOthers(); // Use toOthers() if you don't want the sender to receive it via broadcast

        return $message;
    });

}); // End of auth:sanctum group

// Remove the duplicate chat routes outside the auth:sanctum group if they exist
// ... (ensure no duplicate /user, /users, /messages/{user} routes are defined outside the middleware group)