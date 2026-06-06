<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/status', [ApiController::class, 'status']);
Route::post('/login', [ApiController::class, 'login']);

Route::middleware('api.internal')->group(function () {
    Route::get('/leads', [ApiController::class, 'leadsIndex']);
    Route::post('/leads', [ApiController::class, 'leadsStore']);

    Route::get('/leads/{leadId}/activities', [ApiController::class, 'leadActivitiesIndex']);
    Route::post('/lead-activities', [ApiController::class, 'leadActivitiesStore']);

    Route::get('/payouts', [ApiController::class, 'payoutsIndex']);

    Route::get('/users', [ApiController::class, 'usersIndex']);
});
