<?php

namespace App\Http\Controllers;

use App\Models\BillTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display calendar view
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $currentDate = Carbon::create($year, $month, 1);

        // Get all events for the month
        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        // Timeline events
        $events = BillTimeline::whereBetween('event_date', [$startDate, $endDate])
            ->with('bill')
            ->orderBy('event_date', 'asc')
            ->get()
            ->groupBy(function ($event) {
                return $event->event_date->format('Y-m-d');
            });

        // Upcoming deadlines
        $upcomingDeadlines = BillTimeline::where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays(30))
            ->whereNull('deadline_met')
            ->with('bill')
            ->orderBy('deadline', 'asc')
            ->limit(10)
            ->get();

        // Overdue items
        $overdueDeadlines = BillTimeline::where('deadline', '<', now())
            ->whereNull('deadline_met')
            ->with('bill')
            ->orderBy('deadline', 'desc')
            ->limit(5)
            ->get();

        // This week's activity
        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();

        $thisWeekEvents = BillTimeline::whereBetween('event_date', [$thisWeekStart, $thisWeekEnd])
            ->with('bill')
            ->orderBy('event_date', 'asc')
            ->get();

        return view('calendar.index', compact(
            'events',
            'upcomingDeadlines',
            'overdueDeadlines',
            'thisWeekEvents',
            'currentDate'
        ));
    }

    /**
     * Get events for specific date (AJAX)
     */
    public function events(Request $request)
    {
        $date = $request->input('date');

        $events = BillTimeline::whereDate('event_date', $date)
            ->with('bill')
            ->orderBy('event_date', 'asc')
            ->get();

        return response()->json([
            'date' => $date,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'description' => $event->description,
                    'bill' => [
                        'id' => $event->bill->id,
                        'number' => $event->bill->bill_number,
                        'year' => $event->bill->year,
                        'title' => $event->bill->title,
                    ],
                ];
            }),
        ]);
    }
}
