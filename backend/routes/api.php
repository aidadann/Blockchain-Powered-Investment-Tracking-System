<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\AuditLogController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/investments', [InvestmentController::class, 'index']);
    Route::post('/investments', [InvestmentController::class, 'store'])->middleware('role:investor');
    Route::patch('/investments/{id}/approve', [InvestmentController::class, 'approve'])->middleware('role:admin');
    Route::patch('/investments/{id}/hash', [InvestmentController::class, 'updateHash']);

    Route::get('/audits', [AuditLogController::class, 'index'])->middleware('role:auditor');
});

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'time' => now()
    ]);
});
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
