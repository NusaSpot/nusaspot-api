<?php

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DetectController;
use App\Http\Controllers\API\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('request-otp', [AuthController::class, 'requestOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('first-api', [ApiController::class, 'firstApi']);
Route::get('/login/{provider}', [AuthController::class,'redirectToProvider']);
Route::get('/login/{provider}/callback', [AuthController::class,'handleProviderCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('login-status', [AuthController::class, 'loginStatus']);

    Route::get('profile', [ProfileController::class, 'viewProfile']);
    Route::post('profile', [ProfileController::class, 'storeProfile']);
    
    Route::get('detect', [DetectController::class, 'detect']);
    Route::get('detect-start', [DetectController::class, 'detectStart']);
    Route::get('detect-detail/{detectId}', [DetectController::class, 'detectDetail']);
    Route::post('detect-detail-store/{detectId}', [DetectController::class, 'detectDetailStore']);
    Route::get('detect-detail-delete/{detectId}/{detectDetailId}', [DetectController::class, 'detectDetailDelete']);
    Route::get('detect-finish/{detectId}', [DetectController::class, 'detectFinish']);
});