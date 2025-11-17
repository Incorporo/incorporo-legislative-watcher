<?php

namespace App\Http\Controllers;

use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all user's tags
     */
    public function index()
    {
        $tags = Auth::user()->tags()
            ->withCount('bills')
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags'));
    }

    /**
     * Store a new tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user_tags')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'description' => 'nullable|string|max:255',
        ]);

        $tag = Auth::user()->tags()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#3b82f6',
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tag created successfully',
                'tag' => $tag,
            ], 201);
        }

        return redirect()->route('tags.index')->with('success', 'Tag created successfully');
    }

    /**
     * Display a specific tag with all tagged bills
     */
    public function show(UserTag $tag)
    {
        // Ensure user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $tag->load(['bills' => function ($query) {
            $query->orderBy('bill_tag.created_at', 'desc');
        }]);

        return view('tags.show', compact('tag'));
    }

    /**
     * Update a tag
     */
    public function update(Request $request, UserTag $tag)
    {
        // Ensure user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('user_tags')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($tag->id),
            ],
            'color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'description' => 'nullable|string|max:255',
        ]);

        $tag->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tag updated successfully',
                'tag' => $tag->fresh(),
            ]);
        }

        return redirect()->route('tags.index')->with('success', 'Tag updated successfully');
    }

    /**
     * Delete a tag
     */
    public function destroy(UserTag $tag)
    {
        // Ensure user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $tag->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Tag deleted successfully',
            ]);
        }

        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully');
    }

    /**
     * Attach a tag to a bill
     */
    public function attach(Request $request)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:user_tags,id',
            'bill_id' => 'required|exists:legislative_bills,id',
        ]);

        $tag = UserTag::findOrFail($validated['tag_id']);

        // Ensure user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if already attached
        if ($tag->bills()->where('bill_id', $validated['bill_id'])->exists()) {
            return response()->json([
                'message' => 'Bill already has this tag',
            ], 409);
        }

        $tag->bills()->attach($validated['bill_id']);

        return response()->json([
            'message' => 'Tag attached to bill',
            'tag' => $tag->fresh(),
        ]);
    }

    /**
     * Detach a tag from a bill
     */
    public function detach(Request $request)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:user_tags,id',
            'bill_id' => 'required|exists:legislative_bills,id',
        ]);

        $tag = UserTag::findOrFail($validated['tag_id']);

        // Ensure user owns this tag
        if ($tag->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $tag->bills()->detach($validated['bill_id']);

        return response()->json([
            'message' => 'Tag removed from bill',
        ]);
    }

    /**
     * Get all tags for a specific bill (used in tag selector)
     */
    public function forBill(Request $request, int $billId)
    {
        $userTags = Auth::user()->tags()->get();
        $billTags = UserTag::whereHas('bills', function ($query) use ($billId) {
            $query->where('bill_id', $billId);
        })->where('user_id', Auth::id())->pluck('id')->toArray();

        return response()->json([
            'user_tags' => $userTags,
            'bill_tags' => $billTags,
        ]);
    }
}
