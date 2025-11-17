<?php

namespace App\Http\Controllers;

use App\Models\LegislativeBill;
use App\Models\BillRisk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Compare multiple bills side-by-side
     */
    public function compare(Request $request)
    {
        $billIds = $request->input('bills', []);

        // Ensure we have at least 2 bills to compare
        if (count($billIds) < 2) {
            return redirect()->route('bills.index')->with('error', 'Selectați cel puțin 2 proiecte pentru comparație');
        }

        // Limit to max 3 bills for better UI
        $billIds = array_slice($billIds, 0, 3);

        $bills = LegislativeBill::with([
            'initiators.legislator',
            'timeline',
            'risks',
            'analysis',
            'committeeAssignments.committee'
        ])->findOrFail($billIds);

        // Calculate progress for each bill
        $billsWithProgress = $bills->map(function($bill) {
            return [
                'bill' => $bill,
                'progress' => $this->calculateProgress($bill)
            ];
        });

        return view('bills.compare', compact('billsWithProgress'));
    }

    /**
     * Export bill to PDF
     */
    public function exportPDF($id)
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
            'committeeAssignments.committee'
        ])->findOrFail($id);

        $latestAnalysis = $bill->analysis->where('analysis_type', 'ai_assessment')->first();
        $analysisData = $latestAnalysis?->analysis_result;
        $progressPercentage = $this->calculateProgress($bill);

        $pdf = Pdf::loadView('bills.pdf', compact('bill', 'latestAnalysis', 'analysisData', 'progressPercentage'));

        $filename = 'bill-' . $bill->bill_number . '-' . $bill->year . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export bills to CSV
     */
    public function exportCSV(Request $request)
    {
        $query = LegislativeBill::with(['initiators', 'risks']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('bill_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('chamber')) {
            $query->where('chamber', $request->input('chamber'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        if ($request->filled('urgent')) {
            $query->where('urgency_status', true);
        }

        if ($request->filled('risk')) {
            $riskLevel = $request->input('risk');
            $query->whereHas('risks', function($q) use ($riskLevel) {
                $q->where('risk_level', $riskLevel);
            });
        }

        $bills = $query->orderBy('registration_date', 'desc')->limit(1000)->get();

        $filename = 'legislative-bills-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($bills) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'Bill Number',
                'Year',
                'Title',
                'Chamber',
                'Status',
                'Urgency',
                'Type',
                'Registration Date',
                'Initiators',
                'Risk Level',
                'URL'
            ]);

            // Data rows
            foreach ($bills as $bill) {
                fputcsv($file, [
                    $bill->bill_number,
                    $bill->year,
                    $bill->title,
                    $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat',
                    $bill->status ?? 'N/A',
                    $bill->urgency_status ? 'Yes' : 'No',
                    $bill->type ?? 'N/A',
                    $bill->registration_date?->format('Y-m-d') ?? 'N/A',
                    $bill->initiators->pluck('name')->join('; '),
                    $bill->getHighestRiskLevel() ?? 'N/A',
                    $bill->url ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Share bill - returns shareable link with metadata
     */
    public function share($id)
    {
        $bill = LegislativeBill::with(['initiators', 'analysis'])->findOrFail($id);

        $latestAnalysis = $bill->analysis->where('analysis_type', 'ai_assessment')->first();
        $summary = $latestAnalysis?->analysis_result['summary'] ?? $bill->description ?? 'Proiect de lege din Parlamentul României';

        // Generate shareable data
        $shareData = [
            'url' => route('bills.show', $bill->id),
            'title' => $bill->title,
            'description' => \Str::limit($summary, 200),
            'image' => asset('images/og-image.png'), // You can generate dynamic OG images later
            'bill_number' => $bill->bill_number . '/' . $bill->year,
            'chamber' => $bill->chamber === 'cdep' ? 'Camera Deputaților' : 'Senat',
        ];

        return view('bills.share', compact('bill', 'shareData'));
    }
}
