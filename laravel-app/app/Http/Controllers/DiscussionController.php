<?php

namespace App\Http\Controllers;

use App\Models\BillDiscussion;
use App\Models\DiscussionComment;
use App\Models\LegislativeBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display discussions for a bill
     */
    public function index(LegislativeBill $bill)
    {
        $discussions = $bill->discussions()
            ->with(['creator', 'team'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        return view('discussions.index', compact('bill', 'discussions'));
    }

    /**
     * Create a new discussion
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:legislative_bills,id',
            'team_id' => 'nullable|exists:teams,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'discussion_type' => 'sometimes|in:general,amendment,impact,strategy',
        ]);

        $discussion = BillDiscussion::create([
            'bill_id' => $validated['bill_id'],
            'team_id' => $validated['team_id'] ?? null,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'discussion_type' => $validated['discussion_type'] ?? 'general',
            'last_activity_at' => now(),
        ]);

        return redirect()->route('discussions.show', $discussion)->with('success', 'Discussion created');
    }

    /**
     * Show discussion with comments
     */
    public function show(BillDiscussion $discussion)
    {
        $discussion->incrementViews();
        $discussion->load(['creator', 'bill', 'team', 'topLevelComments.user', 'topLevelComments.replies.user']);

        return view('discussions.show', compact('discussion'));
    }

    /**
     * Add comment to discussion
     */
    public function addComment(Request $request, BillDiscussion $discussion)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:discussion_comments,id',
        ]);

        $comment = $discussion->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $discussion->increment('comment_count');
        $discussion->updateActivity();

        return back()->with('success', 'Comment added');
    }

    /**
     * Toggle comment like
     */
    public function toggleLike(DiscussionComment $comment)
    {
        $liked = $comment->toggleLike(Auth::id());

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }
}
