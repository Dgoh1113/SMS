<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/status', [ApiController::class, 'status']);
Route::post('/login', [ApiController::class, 'login']);

Route::get('/debug/tables', [ApiController::class, 'debugTables']);
Route::get('/debug/columns/{table}', [ApiController::class, 'debugColumns']);

Route::get('/products', [ApiController::class, 'productsIndex']);
Route::post('/products', [ApiController::class, 'productsStore']);

Route::get('/clients-leads', [ApiController::class, 'clientsLeadsIndex']);
Route::post('/clients-leads', [ApiController::class, 'clientsLeadsStore']);

Route::get('/referrers', [ApiController::class, 'referrersIndex']);
Route::post('/referrers', [ApiController::class, 'referrersStore']);

Route::get('/users', [ApiController::class, 'usersIndex']);
Route::post('/users', [ApiController::class, 'usersStore']);

Route::get('/admins', [ApiController::class, 'adminsIndex']);
Route::post('/admins', [ApiController::class, 'adminsStore']);

Route::get('/dealers', [ApiController::class, 'dealersIndex']);
Route::post('/dealers', [ApiController::class, 'dealersStore']);

Route::get('/customer-inquiries', [ApiController::class, 'customerInquiriesIndex']);
Route::post('/customer-inquiries', [ApiController::class, 'customerInquiriesStore']);

Route::get('/admin-interventions', [ApiController::class, 'adminInterventionsIndex']);
Route::post('/admin-interventions', [ApiController::class, 'adminInterventionsStore']);

Route::get('/deals-submissions', [ApiController::class, 'dealsSubmissionsIndex']);
Route::post('/deals-submissions', [ApiController::class, 'dealsSubmissionsStore']);

Route::get('/deal-items', [ApiController::class, 'dealItemsIndex']);
Route::post('/deal-items', [ApiController::class, 'dealItemsStore']);

Route::get('/referrer-payouts', [ApiController::class, 'referrerPayoutsIndex']);
Route::post('/referrer-payouts', [ApiController::class, 'referrerPayoutsStore']);

Route::get('/deal-status-history', [ApiController::class, 'dealStatusHistoryIndex']);
Route::post('/deal-status-history', [ApiController::class, 'dealStatusHistoryStore']);
