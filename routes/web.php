<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PageController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    return "The server is Running";
});
// Public pages required by the stores.
Route::get('/privacy', [PageController::class, 'privacy']);
Route::get('/terms', [PageController::class, 'terms']);
Route::get('/support', [PageController::class, 'support']);
Route::get('/delete-account', [PageController::class, 'deleteAccountInfo']);
Route::post('/delete-account', [PageController::class, 'deleteAccount'])->middleware('throttle:web-account-actions');