<?php

namespace App\Http\Controllers;

use App\Models\BillNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all notes for the authenticated user
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notes()->with('bill');

        // Filter by bill if provided
        if ($request->has('bill_id')) {
            $query->where('bill_id', $request->bill_id);
        }

        // Filter by privacy
        if ($request->has('privacy')) {
            if ($request->privacy === 'private') {
                $query->private();
            } elseif ($request->privacy === 'public') {
                $query->public();
            }
        }

        $notes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('notes.index', compact('notes'));
    }

    /**
     * Get notes for a specific bill
     */
    public function forBill(int $billId)
    {
        $notes = Auth::user()->notes()
            ->where('bill_id', $billId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notes' => $notes,
        ]);
    }

    /**
     * Store a new note
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:legislative_bills,id',
            'content' => 'required|string|max:5000',
            'is_private' => 'sometimes|boolean',
        ]);

        $note = Auth::user()->notes()->create([
            'bill_id' => $validated['bill_id'],
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Note created successfully',
                'note' => $note->fresh()->load('bill'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Note created successfully');
    }

    /**
     * Display a specific note
     */
    public function show(BillNote $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $note->load('bill');

        return view('notes.show', compact('note'));
    }

    /**
     * Update a note
     */
    public function update(Request $request, BillNote $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'content' => 'sometimes|string|max:5000',
            'is_private' => 'sometimes|boolean',
        ]);

        $note->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Note updated successfully',
                'note' => $note->fresh()->load('bill'),
            ]);
        }

        return redirect()->back()->with('success', 'Note updated successfully');
    }

    /**
     * Delete a note
     */
    public function destroy(BillNote $note)
    {
        // Ensure user owns this note
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $note->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Note deleted successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Note deleted successfully');
    }
}
