<?php

namespace App\Http\Controllers;

use App\Models\LegislativeBill;
use App\Models\BillRisk;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display a listing of bills
     */
    public function index(Request $request)
    {
        $query = LegislativeBill::with(['initiators', 'risks']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('bill_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by chamber
        if ($request->filled('chamber')) {
            $query->where('chamber', $request->input('chamber'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter by urgency
        if ($request->filled('urgent')) {
            $query->where('urgency_status', true);
        }

        // Filter by risk level
        if ($request->filled('risk')) {
            $riskLevel = $request->input('risk');
            $query->whereHas('risks', function($q) use ($riskLevel) {
                $q->where('risk_level', $riskLevel);
            });
        }

        // Sorting
        $sortBy = $request->input('sort', 'registration_date');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $bills = $query->paginate(20)->withQueryString();

        // Get available years for filter
        $years = LegislativeBill::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get available statuses for filter
        $statuses = LegislativeBill::selectRaw('DISTINCT status')
            ->whereNotNull('status')
            ->orderBy('status')
            ->pluck('status');

        return view('bills.index', compact('bills', 'years', 'statuses'));
    }

    /**
     * Display the specified bill
     */
    public function show($id)
    {
        $bill = LegislativeBill::with([
            'initiators.legislator',
            'timeline' => function($query) {
                $query->orderBy('event_date', 'desc');
            },
            'documents',
            'risks' => function($query) {
                $query->orderBy('risk_level', 'desc');
            },
            'analysis',
            'changes' => function($query) {
                $query->orderBy('detected_at', 'desc')->limit(10);
            },
            'committeeAssignments.committee'
        ])->findOrFail($id);

        // Get similar bills (same type, similar year)
        $similarBills = LegislativeBill::where('id', '!=', $bill->id)
            ->where('type', $bill->type)
            ->whereBetween('year', [$bill->year - 1, $bill->year + 1])
            ->limit(5)
            ->get();

        // Calculate progress percentage
        $progressPercentage = $this->calculateProgress($bill);

        return view('bills.show', compact('bill', 'similarBills', 'progressPercentage'));
    }

    /**
     * Calculate bill progress percentage
     */
    private function calculateProgress(LegislativeBill $bill)
    {
        $stages = [
            'registered' => 20,
            'committee_review' => 40,
            'debate' => 60,
            'vote' => 80,
            'passed' => 100,
            'promulgated' => 100,
        ];

        $currentStatus = strtolower($bill->status ?? 'registered');

        foreach ($stages as $stage => $percentage) {
            if (str_contains($currentStatus, $stage)) {
                return $percentage;
            }
        }

        return 10; // Default minimal progress
    }

    /**
     * Search bills (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        $bills = LegislativeBill::where('title', 'like', "%{$query}%")
            ->orWhere('bill_number', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['initiators', 'risks'])
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $bills->map(function($bill) {
                return [
                    'id' => $bill->id,
                    'title' => $bill->title,
                    'bill_number' => $bill->bill_number,
                    'year' => $bill->year,
                    'status' => $bill->status,
                    'chamber' => $bill->chamber,
                    'url' => route('bills.show', $bill->id),
                    'risk_level' => $bill->getHighestRiskLevel(),
                ];
            })
        ]);
    }
}
