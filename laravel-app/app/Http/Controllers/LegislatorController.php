<?php

namespace App\Http\Controllers;

use App\Models\Legislator;
use App\Models\LegislativeBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegislatorController extends Controller
{
    /**
     * Display a listing of legislators
     */
    public function index(Request $request)
    {
        $query = Legislator::where('active', true);

        // Filter by chamber
        if ($request->filled('chamber')) {
            $query->where('chamber', $request->input('chamber'));
        }

        // Filter by party
        if ($request->filled('party')) {
            $query->where('party_normalized', $request->input('party'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort', 'bills_initiated');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $legislators = $query->paginate(24)->withQueryString();

        // Get available parties for filter
        $parties = Legislator::where('active', true)
            ->whereNotNull('party_normalized')
            ->distinct('party_normalized')
            ->orderBy('party_normalized')
            ->pluck('party_normalized');

        // Top performers
        $topPerformers = Legislator::where('active', true)
            ->orderBy('bills_initiated', 'desc')
            ->limit(5)
            ->get();

        // Statistics
        $stats = [
            'total_legislators' => Legislator::where('active', true)->count(),
            'cdep_legislators' => Legislator::where('active', true)->where('chamber', 'cdep')->count(),
            'senate_legislators' => Legislator::where('active', true)->where('chamber', 'senate')->count(),
            'total_bills_initiated' => Legislator::where('active', true)->sum('bills_initiated'),
        ];

        // Party distribution
        $partyDistribution = Legislator::where('active', true)
            ->select('party_normalized', DB::raw('count(*) as count'))
            ->whereNotNull('party_normalized')
            ->groupBy('party_normalized')
            ->orderBy('count', 'desc')
            ->get();

        // Add total parties to stats
        $stats['total_parties'] = $partyDistribution->count();

        return view('legislators.index', compact(
            'legislators',
            'parties',
            'topPerformers',
            'stats',
            'partyDistribution'
        ));
    }

    /**
     * Display the specified legislator
     */
    public function show($id)
    {
        $legislator = Legislator::with([
            'initiatedBills.bill',
            'coSponsoredBills.bill',
            'activeCommittees',
            'chairedCommittees'
        ])->findOrFail($id);

        // Get recent bills
        $recentBills = LegislativeBill::whereHas('initiators', function($query) use ($id) {
            $query->where('legislator_id', $id);
        })
        ->with(['risks', 'timeline'])
        ->orderBy('registration_date', 'desc')
        ->limit(10)
        ->get();

        // Calculate statistics
        $stats = [
            'bills_initiated' => $legislator->bills_initiated,
            'bills_co_sponsored' => $legislator->bills_co_sponsored,
            'total_bills' => $legislator->bills_initiated + $legislator->bills_co_sponsored,
            'committees' => $legislator->activeCommittees->count(),
            'chairs' => $legislator->chairedCommittees->count(),
        ];

        // Activity timeline (last 6 months)
        $activityTimeline = LegislativeBill::whereHas('initiators', function($query) use ($id) {
            $query->where('legislator_id', $id);
        })
        ->select(
            DB::raw('DATE_FORMAT(registration_date, "%Y-%m") as month'),
            DB::raw('count(*) as total')
        )
        ->where('registration_date', '>', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        // Success rate (passed bills / total bills)
        $passedBills = LegislativeBill::whereHas('initiators', function($query) use ($id) {
            $query->where('legislator_id', $id);
        })
        ->where('status', 'passed')
        ->count();

        $successRate = $stats['total_bills'] > 0
            ? round(($passedBills / $stats['total_bills']) * 100, 1)
            : 0;

        return view('legislators.show', compact(
            'legislator',
            'recentBills',
            'stats',
            'activityTimeline',
            'successRate'
        ));
    }

    /**
     * Compare legislators
     */
    public function compare(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || count($ids) < 2) {
            return redirect()->route('legislators.index')
                ->with('error', 'Selectează cel puțin 2 legislatori pentru comparație');
        }

        $legislators = Legislator::whereIn('id', $ids)
            ->with(['initiatedBills', 'activeCommittees'])
            ->get();

        return view('legislators.compare', compact('legislators'));
    }
}
