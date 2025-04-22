<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Redirect /home to /dashboard
Route::get('/home', function() {
    return redirect('/dashboard');
});

// Dashboard with user listing
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
