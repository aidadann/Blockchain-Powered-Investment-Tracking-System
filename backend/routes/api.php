<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'time' => now()
    ]);
});
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
