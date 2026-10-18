<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeviceTokenController;
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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/prediction', [PredictionController::class, 'getPrediction']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/profile', [UserController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class,'logout']);
});


Route::middleware('auth:sanctum')->group(function () {



});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
});