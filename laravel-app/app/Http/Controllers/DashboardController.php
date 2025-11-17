<?php

namespace App\Http\Controllers;

use App\Models\LegislativeBill;
use App\Models\Legislator;
use App\Models\BillRisk;
use App\Models\ScrapingJob;
use App\Models\DashboardPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index()
    {
        // Overview statistics
        $stats = [
            'total_bills' => LegislativeBill::count(),
            'active_bills' => LegislativeBill::whereIn('status', [
                'committee_review', 'debate', 'pending'
            ])->count(),
            'urgent_bills' => LegislativeBill::where('urgency_status', true)->count(),
            'high_risk_bills' => BillRisk::where('risk_level', 'critical')
                ->orWhere('risk_level', 'high')
                ->distinct('bill_id')
                ->count('bill_id'),
            'passed_bills' => LegislativeBill::where('status', 'passed')->count(),
            'total_legislators' => Legislator::where('active', true)->count(),
        ];

        // Recent bills
        $recentBills = LegislativeBill::with(['initiators', 'risks'])
            ->orderBy('registration_date', 'desc')
            ->limit(10)
            ->get();

        // High-risk bills
        $highRiskBills = LegislativeBill::whereHas('risks', function($query) {
            $query->whereIn('risk_level', ['critical', 'high']);
        })
        ->with(['risks' => function($query) {
            $query->whereIn('risk_level', ['critical', 'high']);
        }])
        ->orderBy('registration_date', 'desc')
        ->limit(5)
        ->get();

        // Urgent bills
        $urgentBills = LegislativeBill::where('urgency_status', true)
            ->with(['timeline' => function($query) {
                $query->where('deadline', '>', now())
                      ->orderBy('deadline', 'asc')
                      ->limit(1);
            }])
            ->orderBy('registration_date', 'desc')
            ->limit(5)
            ->get();

        // Bills by chamber (for chart)
        $billsByChamber = LegislativeBill::select('chamber', DB::raw('count(*) as total'))
            ->groupBy('chamber')
            ->get()
            ->pluck('total', 'chamber');

        // Bills by status (for chart)
        $billsByStatus = LegislativeBill::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->limit(6)
            ->get();

        // Recent activity (from scraping jobs)
        $lastScrapeJob = ScrapingJob::where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();

        // Bills registered per month (last 6 months)
        // Use database-agnostic date formatting
        $dbDriver = DB::connection()->getDriverName();
        if ($dbDriver === 'sqlite') {
            $dateFormat = "strftime('%Y-%m', registration_date) as month";
        } else {
            $dateFormat = "DATE_FORMAT(registration_date, '%Y-%m') as month";
        }

        $billsPerMonth = LegislativeBill::select(
                DB::raw($dateFormat),
                DB::raw('count(*) as total')
            )
            ->where('registration_date', '>', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentBills',
            'highRiskBills',
            'urgentBills',
            'billsByChamber',
            'billsByStatus',
            'lastScrapeJob',
            'billsPerMonth'
        ));
    }

    /**
     * Get dashboard data for AJAX updates
     */
    public function data()
    {
        return response()->json([
            'stats' => [
                'total_bills' => LegislativeBill::count(),
                'active_bills' => LegislativeBill::whereIn('status', [
                    'committee_review', 'debate', 'pending'
                ])->count(),
                'urgent_bills' => LegislativeBill::where('urgency_status', true)->count(),
                'high_risk_bills' => BillRisk::whereIn('risk_level', ['critical', 'high'])
                    ->distinct('bill_id')
                    ->count('bill_id'),
            ],
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * Show dashboard customization page
     */
    public function customize()
    {
        $preferences = Auth::user()->dashboardPreferences;

        // If no preferences exist, create default ones
        if (!$preferences) {
            $preferences = Auth::user()->dashboardPreferences()->create([
                'widget_layout' => DashboardPreference::getDefaultLayout(),
                'visible_widgets' => DashboardPreference::getDefaultVisibleWidgets(),
                'theme' => 'light',
            ]);
        }

        return view('dashboard.customize', compact('preferences'));
    }

    /**
     * Update dashboard preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'widget_layout' => 'sometimes|array',
            'visible_widgets' => 'sometimes|array',
            'theme' => 'sometimes|in:light,dark,auto',
            'chart_preferences' => 'sometimes|array',
        ]);

        $preferences = Auth::user()->dashboardPreferences;

        if (!$preferences) {
            $preferences = Auth::user()->dashboardPreferences()->create($validated);
        } else {
            $preferences->update($validated);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Dashboard preferences updated',
                'preferences' => $preferences->fresh(),
            ]);
        }

        return redirect()->route('dashboard.index')->with('success', 'Dashboard preferences updated');
    }

    /**
     * Reset dashboard to default layout
     */
    public function resetPreferences()
    {
        $preferences = Auth::user()->dashboardPreferences;

        if ($preferences) {
            $preferences->update([
                'widget_layout' => DashboardPreference::getDefaultLayout(),
                'visible_widgets' => DashboardPreference::getDefaultVisibleWidgets(),
                'theme' => 'light',
                'chart_preferences' => null,
            ]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Dashboard reset to default layout',
                'preferences' => $preferences->fresh(),
            ]);
        }

        return redirect()->route('dashboard.index')->with('success', 'Dashboard reset to default layout');
    }
}
