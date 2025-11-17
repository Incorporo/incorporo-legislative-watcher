<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's teams
     */
    public function index()
    {
        $ownedTeams = Auth::user()->ownedTeams()->with('members')->get();
        $memberTeams = Auth::user()->teams()->with('owner')->get();

        return view('teams.index', compact('ownedTeams', 'memberTeams'));
    }

    /**
     * Create a new team
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:private,public,organization',
            'max_members' => 'sometimes|integer|min:2|max:500',
        ]);

        $team = Auth::user()->ownedTeams()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'max_members' => $validated['max_members'] ?? 50,
        ]);

        // Add owner as first member
        $team->members()->create([
            'user_id' => Auth::id(),
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return redirect()->route('teams.show', $team)->with('success', 'Team created successfully');
    }

    /**
     * Show team details
     */
    public function show(Team $team)
    {
        // Check if user is a member
        if (!$team->hasMember(Auth::id()) && $team->type !== 'public') {
            abort(403, 'You are not a member of this team');
        }

        $team->load(['members.user', 'billCollections', 'discussions', 'tasks']);

        return view('teams.show', compact('team'));
    }

    /**
     * Update team
     */
    public function update(Request $request, Team $team)
    {
        if (!$team->isAdministrator(Auth::id())) {
            abort(403, 'Only team administrators can update team settings');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:private,public,organization',
            'max_members' => 'sometimes|integer|min:2|max:500',
        ]);

        $team->update($validated);

        return redirect()->back()->with('success', 'Team updated successfully');
    }

    /**
     * Delete team
     */
    public function destroy(Team $team)
    {
        if ($team->owner_id !== Auth::id()) {
            abort(403, 'Only the team owner can delete the team');
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully');
    }

    /**
     * Add member to team
     */
    public function addMember(Request $request, Team $team)
    {
        if (!$team->isAdministrator(Auth::id())) {
            abort(403, 'Only administrators can add members');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:member,admin,viewer',
        ]);

        if ($team->isAtCapacity()) {
            return back()->with('error', 'Team is at maximum capacity');
        }

        if ($team->hasMember($validated['user_id'])) {
            return back()->with('error', 'User is already a member');
        }

        $team->members()->create([
            'user_id' => $validated['user_id'],
            'role' => $validated['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Member added successfully');
    }

    /**
     * Remove member from team
     */
    public function removeMember(Team $team, TeamMember $member)
    {
        if (!$team->isAdministrator(Auth::id()) && $member->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($member->role === 'owner') {
            return back()->with('error', 'Cannot remove team owner');
        }

        $member->delete();

        return back()->with('success', 'Member removed successfully');
    }
}
