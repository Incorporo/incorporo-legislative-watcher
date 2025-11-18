<?php

namespace App\Http\Controllers;

use App\Models\BillRisk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskController extends Controller
{
    /**
     * Display risk monitoring dashboard
     */
    public function index(Request $request)
    {
        $query = BillRisk::with(['bill'])
            ->where('status', 'active')
            ->where('public', true);

        // Filter by risk level
        if ($request->filled('level')) {
            $query->where('risk_level', $request->input('level'));
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('risk_category', $request->input('category'));
        }

        // Sorting
        $query->orderByRaw("FIELD(risk_level, 'critical', 'high', 'medium', 'low')")
            ->orderBy('flagged_at', 'desc');

        $risks = $query->paginate(20)->withQueryString();

        // Risk statistics
        $stats = [
            'critical' => BillRisk::where('risk_level', 'critical')
                ->where('status', 'active')
                ->where('public', true)
                ->distinct('bill_id')
                ->count('bill_id'),
            'high' => BillRisk::where('risk_level', 'high')
                ->where('status', 'active')
                ->where('public', true)
                ->distinct('bill_id')
                ->count('bill_id'),
            'medium' => BillRisk::where('risk_level', 'medium')
                ->where('status', 'active')
                ->where('public', true)
                ->distinct('bill_id')
                ->count('bill_id'),
            'low' => BillRisk::where('risk_level', 'low')
                ->where('status', 'active')
                ->where('public', true)
                ->distinct('bill_id')
                ->count('bill_id'),
        ];

        // Risks by category
        $risksByCategory = BillRisk::select('risk_category', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->where('public', true)
            ->groupBy('risk_category')
            ->orderBy('total', 'desc')
            ->get();

        // Available categories for filter
        $categories = BillRisk::selectRaw('DISTINCT risk_category')
            ->where('public', true)
            ->orderBy('risk_category')
            ->pluck('risk_category');

        return view('risks.index', compact('risks', 'stats', 'risksByCategory', 'categories'));
    }

    /**
     * Display specific risk details
     */
    public function show($id)
    {
        $risk = BillRisk::with([
            'bill.initiators',
            'bill.timeline',
            'bill.documents',
            'analysis',
        ])->findOrFail($id);

        return view('risks.show', compact('risk'));
    }
}
