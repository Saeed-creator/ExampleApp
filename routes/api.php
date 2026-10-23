<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\PasswordResetController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');

// Forgot password with an emailed verification code (no authentication needed).
Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:forgot-password');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:reset-password');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/prediction', [PredictionController::class, 'getPrediction']);

    Route::get('/profile', [UserController::class, 'show']);
    Route::patch('/profile', [UserController::class, 'updateProfile']);
    Route::delete('/profile', [UserController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
});
