<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all saved searches
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort', 'last_used_at');

        $query = Auth::user()->savedSearches();

        switch ($sortBy) {
            case 'most_used':
                $query->mostUsed();
                break;
            case 'recently_used':
                $query->recentlyUsed();
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->orderBy('last_used_at', 'desc');
        }

        $savedSearches = $query->get();

        return view('searches.index', compact('savedSearches'));
    }

    /**
     * Store a new saved search
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'filters' => 'required|array',
            'is_default' => 'sometimes|boolean',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Auth::user()->savedSearches()->update(['is_default' => false]);
        }

        $savedSearch = Auth::user()->savedSearches()->create([
            'name' => $validated['name'],
            'filters' => $validated['filters'],
            'is_default' => $validated['is_default'] ?? false,
            'use_count' => 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Search saved successfully',
                'saved_search' => $savedSearch,
            ], 201);
        }

        return redirect()->route('searches.index')->with('success', 'Search saved successfully');
    }

    /**
     * Apply a saved search and increment usage
     */
    public function apply(SavedSearch $savedSearch)
    {
        // Ensure user owns this saved search
        if ($savedSearch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Record the use
        $savedSearch->recordUse();

        // Build query string from filters
        $queryString = http_build_query($savedSearch->filters);

        // Redirect to bills page with filters applied
        return redirect()->route('bills.index', $savedSearch->filters)
            ->with('success', "Applied saved search: {$savedSearch->name}");
    }

    /**
     * Get saved search details (for AJAX)
     */
    public function show(SavedSearch $savedSearch)
    {
        // Ensure user owns this saved search
        if ($savedSearch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'saved_search' => $savedSearch,
        ]);
    }

    /**
     * Update a saved search
     */
    public function update(Request $request, SavedSearch $savedSearch)
    {
        // Ensure user owns this saved search
        if ($savedSearch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'filters' => 'sometimes|array',
            'is_default' => 'sometimes|boolean',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            Auth::user()->savedSearches()
                ->where('id', '!=', $savedSearch->id)
                ->update(['is_default' => false]);
        }

        $savedSearch->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Saved search updated successfully',
                'saved_search' => $savedSearch->fresh(),
            ]);
        }

        return redirect()->route('searches.index')->with('success', 'Search updated successfully');
    }

    /**
     * Delete a saved search
     */
    public function destroy(SavedSearch $savedSearch)
    {
        // Ensure user owns this saved search
        if ($savedSearch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $savedSearch->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Saved search deleted successfully',
            ]);
        }

        return redirect()->route('searches.index')->with('success', 'Search deleted successfully');
    }

    /**
     * Set a search as default
     */
    public function setDefault(SavedSearch $savedSearch)
    {
        // Ensure user owns this saved search
        if ($savedSearch->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Unset all other defaults
        Auth::user()->savedSearches()->update(['is_default' => false]);

        // Set this one as default
        $savedSearch->update(['is_default' => true]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Default search updated',
            ]);
        }

        return redirect()->back()->with('success', 'Default search updated');
    }
}
