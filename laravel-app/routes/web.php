<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SavedSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard routes (with customization)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/customize', [DashboardController::class, 'customize'])->name('dashboard.customize');
    Route::post('/dashboard/preferences', [DashboardController::class, 'updatePreferences'])->name('dashboard.preferences.update');
    Route::post('/dashboard/preferences/reset', [DashboardController::class, 'resetPreferences'])->name('dashboard.preferences.reset');
});

// Legacy dashboard route (backwards compatibility)
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Bills routes
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');

// Authenticated routes - Phase 1 ERP Features
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Watchlist routes
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::patch('/watchlist/{watchlist}', [WatchlistController::class, 'update'])->name('watchlist.update');
    Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
    Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

    // Tags routes
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::post('/tags/attach', [TagController::class, 'attach'])->name('tags.attach');
    Route::post('/tags/detach', [TagController::class, 'detach'])->name('tags.detach');
    Route::get('/tags/bill/{billId}', [TagController::class, 'forBill'])->name('tags.forBill');

    // Notes routes
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::patch('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('/notes/bill/{billId}', [NoteController::class, 'forBill'])->name('notes.forBill');

    // Saved Searches routes
    Route::get('/searches', [SavedSearchController::class, 'index'])->name('searches.index');
    Route::post('/searches', [SavedSearchController::class, 'store'])->name('searches.store');
    Route::get('/searches/{savedSearch}', [SavedSearchController::class, 'show'])->name('searches.show');
    Route::patch('/searches/{savedSearch}', [SavedSearchController::class, 'update'])->name('searches.update');
    Route::delete('/searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('searches.destroy');
    Route::get('/searches/{savedSearch}/apply', [SavedSearchController::class, 'apply'])->name('searches.apply');
    Route::post('/searches/{savedSearch}/set-default', [SavedSearchController::class, 'setDefault'])->name('searches.setDefault');

    // Phase 3: Team Collaboration routes
    Route::get('/teams', [App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [App\Http\Controllers\TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [App\Http\Controllers\TeamController::class, 'show'])->name('teams.show');
    Route::patch('/teams/{team}', [App\Http\Controllers\TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [App\Http\Controllers\TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/members', [App\Http\Controllers\TeamController::class, 'addMember'])->name('teams.addMember');
    Route::delete('/teams/{team}/members/{member}', [App\Http\Controllers\TeamController::class, 'removeMember'])->name('teams.removeMember');

    // Discussions routes
    Route::post('/discussions', [App\Http\Controllers\DiscussionController::class, 'store'])->name('discussions.store');
    Route::post('/discussions/{discussion}/comments', [App\Http\Controllers\DiscussionController::class, 'addComment'])->name('discussions.addComment');
    Route::post('/comments/{comment}/like', [App\Http\Controllers\DiscussionController::class, 'toggleLike'])->name('comments.toggleLike');
});

// Public discussion routes (no auth required)
Route::get('/bills/{bill}/discussions', [App\Http\Controllers\DiscussionController::class, 'index'])->name('discussions.index');
Route::get('/discussions/{discussion}', [App\Http\Controllers\DiscussionController::class, 'show'])->name('discussions.show');

require __DIR__.'/auth.php';
