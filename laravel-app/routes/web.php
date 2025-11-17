<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\LegislatorController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommitteeController;
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
Route::get('/bills/export/csv', [BillController::class, 'exportCSV'])->name('bills.export.csv');
Route::get('/bills/{id}', [BillController::class, 'show'])->name('bills.show');
Route::get('/bills/{id}/export/pdf', [BillController::class, 'exportPDF'])->name('bills.export.pdf');
Route::get('/bills/{id}/share', [BillController::class, 'share'])->name('bills.share');
Route::get('/bills/compare', [BillController::class, 'compare'])->name('bills.compare');
Route::get('/api/bills/search', [BillController::class, 'search'])->name('bills.search');

// Risks
Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');
Route::get('/risks/{id}', [RiskController::class, 'show'])->name('risks.show');

// Legislators
Route::get('/legislators', [LegislatorController::class, 'index'])->name('legislators.index');
Route::get('/legislators/{id}', [LegislatorController::class, 'show'])->name('legislators.show');
Route::get('/legislators/compare', [LegislatorController::class, 'compare'])->name('legislators.compare');

// Calendar
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

// Committees
Route::get('/committees', [CommitteeController::class, 'index'])->name('committees.index');
Route::get('/committees/{id}', [CommitteeController::class, 'show'])->name('committees.show');

// API Documentation
Route::get('/api', function () {
    return response()->json([
        'message' => 'Romanian Legislative Watcher API',
        'version' => '1.0',
        'endpoints' => [
            'GET /api/bills/search' => 'Search bills',
            'GET /dashboard/data' => 'Get dashboard statistics',
            'GET /calendar/events' => 'Get calendar events for specific date',
        ]
    ]);
})->name('api.index');
