<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\AuditLogController;

// =====================================================
// PROTECTED ROUTES (requires authentication)
// =====================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/investments', [InvestmentController::class, 'index']);
    Route::post('/investments', [InvestmentController::class, 'store'])->middleware('role:investor');
    Route::patch('/investments/{id}/approve', [InvestmentController::class, 'approve'])->middleware('role:admin');
    Route::patch('/investments/{id}/reject', [InvestmentController::class, 'reject'])->middleware('role:admin');
    Route::patch('/investments/{id}/hash', [InvestmentController::class, 'updateHash']);
    Route::delete('/investments/{id}', [InvestmentController::class, 'destroy'])->middleware('role:investor');

    Route::get('/audits', [AuditLogController::class, 'index'])->middleware('role:auditor');
});

// =====================================================
// PUBLIC ROUTES (no authentication required)
// =====================================================
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'time' => now()
    ]);
});

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Email Verification
Route::get('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);

// Password Reset
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
