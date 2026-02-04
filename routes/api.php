<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes (Must be logged in)
Route::middleware('auth:sanctum')->group(function () {
    // Transactions
    Route::apiResource('transactions', TransactionController::class);

    // Reports & Analytics
    Route::get('/stats/summary', [TransactionController::class, 'summary']);
    Route::get('/stats/by-category', [TransactionController::class, 'byCategory']);

    // Other protected resources
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);

    // User Profile
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
});
