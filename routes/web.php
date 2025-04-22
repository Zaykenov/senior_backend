<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Redirect /home to /dashboard
Route::get('/home', function() {
    return redirect('/dashboard');
});

// Group routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat');

    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages/{user}', [ChatController::class, 'sendMessage']);
});
