<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RiskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

// Bills
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/{id}', [BillController::class, 'show'])->name('bills.show');
Route::get('/api/bills/search', [BillController::class, 'search'])->name('bills.search');

// Risks
Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');
Route::get('/risks/{id}', [RiskController::class, 'show'])->name('risks.show');

// Placeholder routes for future features
Route::get('/calendar', function () {
    return view('placeholder', ['title' => 'Calendar', 'message' => 'Coming soon - Calendar view']);
})->name('calendar');

Route::get('/legislators', function () {
    return view('placeholder', ['title' => 'Legislatori', 'message' => 'Coming soon - Legislator profiles']);
})->name('legislators.index');

Route::get('/api', function () {
    return response()->json([
        'message' => 'Romanian Legislative Watcher API',
        'version' => '1.0',
        'endpoints' => [
            'GET /api/bills' => 'List all bills',
            'GET /api/bills/{id}' => 'Get bill details',
            'GET /api/risks' => 'List all risks',
            'GET /dashboard/data' => 'Get dashboard statistics',
        ]
    ]);
})->name('api.index');
