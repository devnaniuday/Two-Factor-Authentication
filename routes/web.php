<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwoFactorAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', '2faCheck'])->name('dashboard');

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('2faCheck');
Route::post('login', [AuthController::class, 'login']);
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register')->middleware('2faCheck');
Route::post('register', [AuthController::class, 'register']);
Route::any('logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::get('2fa-setup', [TwoFactorAuthController::class, 'show2FASetupForm'])->name('2fa.setup')->middleware('2faCheck');
    Route::post('2fa-setup', [TwoFactorAuthController::class, 'store2FA'])->name('2fa.store');
    Route::get('/2fa/verify', [TwoFactorAuthController::class, 'showVerifyForm'])->name('2fa.verify')->middleware('2faCheck');
    Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verify']);
    Route::post('/2fa/verify-recovery', [TwoFactorAuthController::class, 'verifyRecovery'])->name('2fa.verifyRecovery');
    Route::post('/verify-password', [AuthController::class, 'verifyPassword'])->name('verify-password');
    Route::post('/recovery-codes', [AuthController::class, 'showRecoveryCodes'])->name('recovery-codes');
    Route::post('/regenerate-recovery-codes', [AuthController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
});
