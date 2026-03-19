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
Route::post('/password/forgot', [AuthController::class, 'requestPasswordResetFromLogin'])->name('password.forgot');
Route::get('/password/force-change', [AuthController::class, 'showForceChangePasswordForm'])->name('password.force-change.form');
Route::post('/password/force-change', [AuthController::class, 'forceChangePassword'])->name('password.force-change.submit');
Route::get('/password/reset/{userid}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('/password/reset/{userid}', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

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
    Route::get('/inquiries/assigned-page', [AdminController::class, 'inquiriesAssignedPage'])->name('inquiries.assigned-page');
    Route::get('/inquiries/create', [AdminController::class, 'createInquiry'])->name('inquiries.create');
    Route::get('/inquiries/{leadId}/edit', [AdminController::class, 'editInquiry'])->name('inquiries.edit');
    Route::post('/inquiries', [AdminController::class, 'storeInquiry'])->name('inquiries.store');
    Route::put('/inquiries/{leadId}', [AdminController::class, 'updateInquiry'])->name('inquiries.update');
    Route::post('/inquiries/{leadId}/delete', [AdminController::class, 'deleteInquiry'])->name('inquiries.delete');
    Route::post('/inquiries/delete/undo', [AdminController::class, 'undoDeleteInquiry'])->name('inquiries.delete-undo');
    Route::post('/inquiries/assign', [AdminController::class, 'assignInquiry'])->name('inquiries.assign');
    Route::post('/inquiries/assign/undo', [AdminController::class, 'undoAssignInquiry'])->name('inquiries.assign-undo');
    Route::post('/inquiries/send-assignment-email', [AdminController::class, 'sendAssignmentEmail'])->name('inquiries.send-assignment-email');
    Route::post('/inquiries/mark-failed', [AdminController::class, 'markInquiryFailed'])->name('inquiries.mark-failed');
    Route::get('/inquiries/company-lookup', [AdminController::class, 'companyLookup'])->name('inquiries.company-lookup');
    Route::get('/inquiries/{leadId}/status', [AdminController::class, 'leadStatus'])->name('inquiries.lead-status');
    Route::get('/dealers', [AdminController::class, 'dealers'])->name('dealers');
    Route::post('/dealers', [AdminController::class, 'dealersStore'])->name('dealers.store');
    Route::post('/dealers/{userId}', [AdminController::class, 'dealersUpdate'])->name('dealers.update');
    Route::get('/rewards', [AdminController::class, 'rewards'])->name('rewards');
    Route::get('/rewards/serve-attachment', [AdminController::class, 'serveRewardAttachment'])->name('rewards.serve-attachment');
    Route::get('/rewards/{leadId}/activity-attachment/{leadActId}', [AdminController::class, 'rewardActivityAttachment'])->name('rewards.activity-attachment');
    Route::post('/rewards/send-email', [AdminController::class, 'sendPayoutEmail'])->name('rewards.send-email');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports-v2', [AdminController::class, 'reportsV2'])->name('reports.v2');
    Route::get('/reports/dealer-activity/{userid}', [AdminController::class, 'dealerActivity'])->name('reports.dealer-activity');
    Route::get('/reports-revenue', [AdminController::class, 'reportsRevenue'])->name('reports.revenue');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
    Route::get('/maintain-users', [AdminController::class, 'maintainUsers'])->name('maintain-users');
    Route::post('/maintain-users', [AdminController::class, 'maintainUsersStore'])->name('maintain-users.store');
    Route::post('/maintain-users/send-temp-passwords', [AdminController::class, 'maintainUsersSendTempPasswords'])->name('maintain-users.send-temp-passwords');
    Route::post('/maintain-users/{userid}/send-temp-password', [AdminController::class, 'maintainUsersSendTempPassword'])->name('maintain-users.send-temp-password');
    Route::put('/maintain-users/{userid}', [AdminController::class, 'maintainUsersUpdate'])->name('maintain-users.update');
});

Route::middleware(['auth.sms', 'dealer'])->prefix('dealer')->name('dealer.')->group(function () {
    Route::get('/dashboard', [DealerController::class, 'dashboard'])->name('dashboard');
    Route::get('/notifications', [DealerController::class, 'notifications'])->name('notifications');
    Route::get('/inquiries', [DealerController::class, 'inquiries'])->name('inquiries');
    Route::get('/inquiries/sync', [DealerController::class, 'inquiriesSync'])->name('inquiries.sync');
    Route::get('/inquiries/{leadId}/activity', [DealerController::class, 'inquiryActivity'])->name('inquiries.activity');
    Route::get('/inquiries/serve-attachment', [DealerController::class, 'serveInquiryAttachment'])->name('inquiries.serve-attachment');
    Route::get('/inquiries/{leadId}/activity-attachment/{leadActId}', [DealerController::class, 'inquiryActivityAttachment'])->name('inquiries.activity-attachment');
    Route::get('/inquiries/{leadId}/failed-description', [DealerController::class, 'inquiryFailedDescription'])->name('inquiries.failed-description');
    Route::post('/inquiries/update-status', [DealerController::class, 'updateInquiryStatus'])->name('inquiries.update-status');
    Route::get('/demo', [DealerController::class, 'demo'])->name('demo');
    Route::get('/payouts', [DealerController::class, 'payouts'])->name('payouts');
    Route::get('/payouts/sync', [DealerController::class, 'payoutsSync'])->name('payouts.sync');
    Route::get('/rewards', fn () => redirect()->route('dealer.payouts'))->name('rewards');
    Route::get('/reports', [DealerController::class, 'reports'])->name('reports');
    Route::get('/history', [DealerController::class, 'history'])->name('history');
});
