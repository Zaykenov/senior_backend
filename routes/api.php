<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AlumniController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SystemController;
use App\Http\Controllers\API\EventController;
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
    
    // Alumni routes - explicit definition with {id} parameter
    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('alumni.store');
    Route::get('/alumni/{id}', [AlumniController::class, 'show'])->name('alumni.show');
    Route::put('/alumni/{id}', [AlumniController::class, 'update'])->name('alumni.update');
    Route::delete('/alumni/{id}', [AlumniController::class, 'destroy'])->name('alumni.destroy');
    
    // Role management routes (admin only)
    Route::apiResource('roles', RoleController::class);
    
    // System routes
    Route::prefix('system')->group(function () {
        Route::get('/activity-logs', [SystemController::class, 'activityLogs'])
            ->middleware('permission:system:view-logs');
        Route::get('/statistics', [SystemController::class, 'statistics'])
            ->middleware('permission:system:view-logs');
    });
  
    // Event routes
    Route::middleware('can:create,App\Models\Event')->group(function () {
        Route::get('/admin/events', [EventController::class, 'index']);
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
        Route::get('/events/{id}/attendees', [EventController::class, 'attendees']);
    });

    // Alumni routes
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events/{id}/register', [EventController::class, 'register']);
    Route::delete('/events/{id}/register', [EventController::class, 'cancelRegistration']);
    Route::get('/my-events', [EventController::class, 'myEvents']);

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

});