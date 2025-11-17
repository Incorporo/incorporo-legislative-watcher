<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeAssignment;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    /**
     * Display a listing of committees
     */
    public function index(Request $request)
    {
        $query = Committee::where('active', true);

        // Filter by chamber
        if ($request->filled('chamber')) {
            $query->where('chamber', $request->input('chamber'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_short', 'like', "%{$search}%");
            });
        }

        $committees = $query->with(['chair', 'members'])
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Statistics
        $stats = [
            'total_committees' => Committee::where('active', true)->count(),
            'cdep_committees' => Committee::where('active', true)->where('chamber', 'cdep')->count(),
            'senate_committees' => Committee::where('active', true)->where('chamber', 'senate')->count(),
            'joint_committees' => Committee::where('active', true)->where('chamber', 'joint')->count(),
        ];

        return view('committees.index', compact('committees', 'stats'));
    }

    /**
     * Display the specified committee
     */
    public function show($id)
    {
        $committee = Committee::with([
            'chair',
            'members' => function ($query) {
                $query->wherePivot('active', true);
            },
            'bills' => function ($query) {
                $query->orderBy('committee_assignments.assigned_date', 'desc');
            },
        ])->findOrFail($id);

        // Current assignments
        $currentAssignments = CommitteeAssignment::where('committee_id', $id)
            ->whereIn('status', ['assigned', 'under_review'])
            ->with('bill')
            ->orderBy('assigned_date', 'desc')
            ->limit(10)
            ->get();

        // Completed assignments
        $completedAssignments = CommitteeAssignment::where('committee_id', $id)
            ->where('status', 'reported')
            ->with('bill')
            ->orderBy('report_date', 'desc')
            ->limit(10)
            ->get();

        // Statistics
        $stats = [
            'total_members' => $committee->members->count(),
            'bills_assigned' => CommitteeAssignment::where('committee_id', $id)->count(),
            'bills_pending' => CommitteeAssignment::where('committee_id', $id)
                ->whereIn('status', ['assigned', 'under_review'])
                ->count(),
            'bills_completed' => CommitteeAssignment::where('committee_id', $id)
                ->where('status', 'reported')
                ->count(),
        ];

        return view('committees.show', compact(
            'committee',
            'currentAssignments',
            'completedAssignments',
            'stats'
        ));
    }
}
