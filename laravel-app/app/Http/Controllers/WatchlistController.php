<?php

namespace App\Http\Controllers;

use App\Models\LegislativeBill;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's watchlist ("My Bills" page)
     */
    public function index(Request $request)
    {
        $query = Auth::user()->watchlist()->with('bill');

        // Filter by priority if requested
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Sort by various criteria
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        if ($sortBy === 'priority') {
            // Custom priority order: high, normal, low
            $query->orderByRaw("FIELD(priority, 'high', 'normal', 'low')");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $watchedBills = $query->paginate(20);

        return view('watchlist.index', compact('watchedBills'));
    }

    /**
     * Add a bill to the user's watchlist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:legislative_bills,id',
            'priority' => 'sometimes|in:low,normal,high',
            'notifications_enabled' => 'sometimes|boolean',
            'personal_note' => 'nullable|string|max:1000',
        ]);

        // Check if already watching
        $existing = Auth::user()->watchlist()->where('bill_id', $validated['bill_id'])->first();

        if ($existing) {
            return response()->json([
                'message' => 'Bill is already in your watchlist',
                'watchlist_id' => $existing->id,
            ], 409);
        }

        $watchlist = Auth::user()->watchlist()->create([
            'bill_id' => $validated['bill_id'],
            'priority' => $validated['priority'] ?? 'normal',
            'notifications_enabled' => $validated['notifications_enabled'] ?? true,
            'personal_note' => $validated['personal_note'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Bill added to watchlist',
                'watchlist' => $watchlist->load('bill'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Bill added to your watchlist');
    }

    /**
     * Update watchlist entry (change priority, notifications, note)
     */
    public function update(Request $request, Watchlist $watchlist)
    {
        // Ensure user owns this watchlist entry
        if ($watchlist->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'priority' => 'sometimes|in:low,normal,high',
            'notifications_enabled' => 'sometimes|boolean',
            'personal_note' => 'nullable|string|max:1000',
        ]);

        $watchlist->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Watchlist entry updated',
                'watchlist' => $watchlist->fresh()->load('bill'),
            ]);
        }

        return redirect()->back()->with('success', 'Watchlist entry updated');
    }

    /**
     * Remove a bill from the user's watchlist
     */
    public function destroy(Watchlist $watchlist)
    {
        // Ensure user owns this watchlist entry
        if ($watchlist->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $watchlist->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Bill removed from watchlist',
            ]);
        }

        return redirect()->back()->with('success', 'Bill removed from watchlist');
    }

    /**
     * Toggle watchlist status for a bill (AJAX endpoint)
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:legislative_bills,id',
        ]);

        $watchlist = Auth::user()->watchlist()->where('bill_id', $validated['bill_id'])->first();

        if ($watchlist) {
            $watchlist->delete();
            return response()->json([
                'watching' => false,
                'message' => 'Bill removed from watchlist',
            ]);
        } else {
            $watchlist = Auth::user()->watchlist()->create([
                'bill_id' => $validated['bill_id'],
                'priority' => 'normal',
                'notifications_enabled' => true,
            ]);

            return response()->json([
                'watching' => true,
                'message' => 'Bill added to watchlist',
                'watchlist_id' => $watchlist->id,
            ]);
        }
    }
}
