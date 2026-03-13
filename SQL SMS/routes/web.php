<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\PasskeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/passkey/auth/options', [PasskeyController::class, 'authOptions'])->name('passkey.auth.options');
Route::post('/passkey/auth/verify', [PasskeyController::class, 'authVerify'])->name('passkey.auth.verify');
Route::post('/passkey/register/options', [PasskeyController::class, 'registerOptions'])->name('passkey.register.options');
Route::post('/passkey/register/verify', [PasskeyController::class, 'registerVerify'])->name('passkey.register.verify');

Route::middleware(['auth.sms', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::get('/dealers', [AdminController::class, 'dealers'])->name('dealers');
    Route::get('/rewards', [AdminController::class, 'rewards'])->name('rewards');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
    Route::get('/fulldatabase', [AdminController::class, 'fulldatabase'])->name('fulldatabase');
});

Route::middleware(['auth.sms', 'dealer'])->prefix('dealer')->name('dealer.')->group(function () {
    Route::get('/dashboard', [DealerController::class, 'dashboard'])->name('dashboard');
    Route::get('/demo', [DealerController::class, 'demo'])->name('demo');
});
