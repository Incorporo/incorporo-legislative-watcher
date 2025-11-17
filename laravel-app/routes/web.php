<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\LegislatorController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DiscussionController;
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

// Welcome page - redirect to bills (public browsing)
Route::get('/', function () {
    return redirect()->route('bills.index');
});

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Bills (Public - anyone can browse legislation)
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
Route::get('/bills/{bill}/discussions', [DiscussionController::class, 'index'])->name('discussions.index');

// Legislators (Public - transparency)
Route::get('/legislators', [LegislatorController::class, 'index'])->name('legislators.index');
Route::get('/legislators/{legislator}', [LegislatorController::class, 'show'])->name('legislators.show');

// Committees (Public - transparency)
Route::get('/committees', [CommitteeController::class, 'index'])->name('committees.index');
Route::get('/committees/{committee}', [CommitteeController::class, 'show'])->name('committees.show');

// Calendar (Public - upcoming legislative events)
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

// Risks (Public - transparency about identified risks)
Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');

// Email Subscriptions (MUST be public for email verification links)
Route::get('/subscribe', [SubscriptionController::class, 'create'])->name('subscriptions.create');
Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscriptions.store');
Route::get('/subscribe/verify/{token}', [SubscriptionController::class, 'verify'])->name('subscriptions.verify');
Route::get('/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])->name('subscriptions.unsubscribe');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Login Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/customize', [DashboardController::class, 'customize'])->name('dashboard.customize');
    Route::post('/dashboard/preferences', [DashboardController::class, 'updatePreferences'])->name('dashboard.preferences.update');
    Route::post('/dashboard/preferences/reset', [DashboardController::class, 'resetPreferences'])->name('dashboard.preferences.reset');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Watchlist (Personal Bill Tracking)
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::patch('/watchlist/{watchlist}', [WatchlistController::class, 'update'])->name('watchlist.update');
    Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
    Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

    // Tags (Custom Organization)
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::post('/tags/attach', [TagController::class, 'attach'])->name('tags.attach');
    Route::post('/tags/detach', [TagController::class, 'detach'])->name('tags.detach');
    Route::get('/tags/bill/{billId}', [TagController::class, 'forBill'])->name('tags.forBill');

    // Notes (Personal Bill Notes)
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::patch('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('/notes/bill/{billId}', [NoteController::class, 'forBill'])->name('notes.forBill');

    // Saved Searches (Quick Filters)
    Route::get('/searches', [SavedSearchController::class, 'index'])->name('searches.index');
    Route::post('/searches', [SavedSearchController::class, 'store'])->name('searches.store');
    Route::get('/searches/{savedSearch}', [SavedSearchController::class, 'show'])->name('searches.show');
    Route::patch('/searches/{savedSearch}', [SavedSearchController::class, 'update'])->name('searches.update');
    Route::delete('/searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('searches.destroy');
    Route::get('/searches/{savedSearch}/apply', [SavedSearchController::class, 'apply'])->name('searches.apply');
    Route::post('/searches/{savedSearch}/set-default', [SavedSearchController::class, 'setDefault'])->name('searches.setDefault');

    // Subscription Management (Authenticated users managing their subscriptions)
    Route::get('/subscriptions/manage', [SubscriptionController::class, 'manage'])->name('subscriptions.manage');
    Route::patch('/subscriptions', [SubscriptionController::class, 'update'])->name('subscriptions.update');

    // Teams (Collaboration)
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.addMember');
    Route::delete('/teams/{team}/members/{member}', [TeamController::class, 'removeMember'])->name('teams.removeMember');

    // Discussions (Authenticated - Creating and commenting)
    Route::post('/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::post('/discussions/{discussion}/comments', [DiscussionController::class, 'addComment'])->name('discussions.addComment');
    Route::post('/comments/{comment}/like', [DiscussionController::class, 'toggleLike'])->name('comments.toggleLike');
});

// Public discussion viewing (no auth required to read)
Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');

require __DIR__.'/auth.php';
