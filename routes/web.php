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

Route::middleware(['auth.sms'])->group(function () {
    Route::get('/passkey/register', [PasskeyController::class, 'showRegisterForm'])->name('passkey.register.form');
    Route::post('/passkey/register/options', [PasskeyController::class, 'registerOptions'])->name('passkey.register.options');
    Route::post('/passkey/register/verify', [PasskeyController::class, 'registerVerify'])->name('passkey.register.verify');
});

Route::middleware(['auth.sms', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::get('/inquiries/sync', [AdminController::class, 'inquiriesSync'])->name('inquiries.sync');
    Route::get('/inquiries/create', [AdminController::class, 'createInquiry'])->name('inquiries.create');
    Route::post('/inquiries', [AdminController::class, 'storeInquiry'])->name('inquiries.store');
    Route::post('/inquiries/assign', [AdminController::class, 'assignInquiry'])->name('inquiries.assign');
    Route::post('/inquiries/mark-failed', [AdminController::class, 'markInquiryFailed'])->name('inquiries.mark-failed');
    Route::get('/inquiries/{leadId}/status', [AdminController::class, 'leadStatus'])->name('inquiries.lead-status');
    Route::get('/dealers', [AdminController::class, 'dealers'])->name('dealers');
    Route::post('/dealers', [AdminController::class, 'dealersStore'])->name('dealers.store');
    Route::post('/dealers/{userId}', [AdminController::class, 'dealersUpdate'])->name('dealers.update');
    Route::get('/rewards', [AdminController::class, 'rewards'])->name('rewards');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports-v2', [AdminController::class, 'reportsV2'])->name('reports.v2');
    Route::get('/reports-revenue', [AdminController::class, 'reportsRevenue'])->name('reports.revenue');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
    Route::get('/fulldatabase', [AdminController::class, 'fulldatabase'])->name('fulldatabase');
});

Route::middleware(['auth.sms', 'dealer'])->prefix('dealer')->name('dealer.')->group(function () {
    Route::get('/dashboard', [DealerController::class, 'dashboard'])->name('dashboard');
    Route::get('/inquiries', [DealerController::class, 'inquiries'])->name('inquiries');
    Route::get('/inquiries/{leadId}/activity', [DealerController::class, 'inquiryActivity'])->name('inquiries.activity');
    Route::get('/inquiries/{leadId}/failed-description', [DealerController::class, 'inquiryFailedDescription'])->name('inquiries.failed-description');
    Route::post('/inquiries/update-status', [DealerController::class, 'updateInquiryStatus'])->name('inquiries.update-status');
    Route::get('/demo', [DealerController::class, 'demo'])->name('demo');
    Route::get('/rewards', [DealerController::class, 'rewards'])->name('rewards');
    Route::get('/reports', [DealerController::class, 'reports'])->name('reports');
    Route::get('/history', [DealerController::class, 'history'])->name('history');
});
