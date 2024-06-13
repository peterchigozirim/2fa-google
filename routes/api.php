<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TwoFactorAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/2fa/setup', [TwoFactorAuthController::class, 'setup2FA']);
Route::post('/2fa/enable', [TwoFactorAuthController::class, 'enable2FA']);
Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verify2FA']);
Route::post('/2fa/disable', [TwoFactorAuthController::class, 'disable2FA']);
