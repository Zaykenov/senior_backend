<?php

use App\Http\Controllers\AlumniController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () { // Routes protected by Sanctum
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('alumnis', AlumniController::class); // Creates routes for index, store, show, update, destroy
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
