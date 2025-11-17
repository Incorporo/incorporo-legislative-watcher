<?php

namespace App\Http\Controllers;

use App\Models\BillSubscription;
use App\Models\LegislativeBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionVerification;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Show subscription form
     */
    public function create()
    {
        // Get filter options for form
        $chambers = [
            'cdep' => 'Camera Deputaților',
            'senat' => 'Senat'
        ];

        $statuses = [
            'registered' => 'Înregistrat',
            'in_committee' => 'În comisie',
            'plenary_debate' => 'Dezbatere plenară',
            'voted' => 'Votat',
            'promulgated' => 'Promulgat',
            'rejected' => 'Respins'
        ];

        $riskLevels = [
            'low' => 'Scăzut',
            'medium' => 'Mediu',
            'high' => 'Ridicat',
            'critical' => 'Critic'
        ];

        $frequencies = [
            'instant' => 'Instant (la fiecare proiect)',
            'daily' => 'Zilnic (rezumat dimineața)',
            'weekly' => 'Săptămânal (luni dimineața)'
        ];

        return view('subscriptions.create', compact('chambers', 'statuses', 'riskLevels', 'frequencies'));
    }

    /**
     * Store new subscription
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'chambers' => 'nullable|array',
            'chambers.*' => 'in:cdep,senat',
            'statuses' => 'nullable|array',
            'statuses.*' => 'string',
            'urgent_only' => 'nullable|boolean',
            'risk_level' => 'nullable|in:low,medium,high,critical',
            'frequency' => 'required|in:instant,daily,weekly',
            'include_ai_summary' => 'nullable|boolean',
            'preferred_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if subscription already exists for this email
        $existingSubscription = BillSubscription::where('email', $request->email)
            ->where('active', true)
            ->first();

        if ($existingSubscription) {
            return back()->with('error', 'Există deja o subscriere activă pentru acest email. Verificați-vă inbox-ul pentru link-ul de gestionare.');
        }

        // Parse keywords from comma-separated string
        $keywords = null;
        if ($request->filled('keywords')) {
            $keywords = array_map('trim', explode(',', $request->keywords));
            $keywords = array_filter($keywords); // Remove empty strings
        }

        // Create subscription
        $subscription = BillSubscription::create([
            'email' => $request->email,
            'name' => $request->name,
            'keywords' => $keywords,
            'chambers' => $request->chambers,
            'statuses' => $request->statuses,
            'urgent_only' => $request->boolean('urgent_only'),
            'risk_level' => $request->risk_level,
            'frequency' => $request->frequency,
            'include_ai_summary' => $request->boolean('include_ai_summary', true),
            'preferred_time' => $request->preferred_time ?? '09:00:00',
        ]);

        // Send verification email
        try {
            Mail::to($subscription->email)->send(new SubscriptionVerification($subscription));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            // Don't fail the subscription creation if email fails
        }

        return view('subscriptions.pending', compact('subscription'));
    }

    /**
     * Verify email address
     */
    public function verify($token)
    {
        $subscription = BillSubscription::where('verification_token', $token)->first();

        if (!$subscription) {
            return view('subscriptions.error', [
                'message' => 'Token de verificare invalid sau expirat.'
            ]);
        }

        if ($subscription->isVerified()) {
            return view('subscriptions.already-verified', compact('subscription'));
        }

        $subscription->verify();

        // Get sample matching bills to show user
        $sampleBills = $subscription->getMatchingBills(5);

        return view('subscriptions.verified', compact('subscription', 'sampleBills'));
    }

    /**
     * Show subscription management page
     */
    public function manage($token)
    {
        $subscription = BillSubscription::where('unsubscribe_token', $token)->first();

        if (!$subscription) {
            return view('subscriptions.error', [
                'message' => 'Subscriere nu a fost găsită.'
            ]);
        }

        // Get filter options
        $chambers = [
            'cdep' => 'Camera Deputaților',
            'senat' => 'Senat'
        ];

        $statuses = [
            'registered' => 'Înregistrat',
            'in_committee' => 'În comisie',
            'plenary_debate' => 'Dezbatere plenară',
            'voted' => 'Votat',
            'promulgated' => 'Promulgat',
            'rejected' => 'Respins'
        ];

        $riskLevels = [
            'low' => 'Scăzut',
            'medium' => 'Mediu',
            'high' => 'Ridicat',
            'critical' => 'Critic'
        ];

        $frequencies = [
            'instant' => 'Instant (la fiecare proiect)',
            'daily' => 'Zilnic (rezumat dimineața)',
            'weekly' => 'Săptămânal (luni dimineața)'
        ];

        // Get recent matching bills
        $recentBills = $subscription->getMatchingBills(10);

        return view('subscriptions.manage', compact('subscription', 'chambers', 'statuses', 'riskLevels', 'frequencies', 'recentBills'));
    }

    /**
     * Update subscription preferences
     */
    public function update(Request $request, $token)
    {
        $subscription = BillSubscription::where('unsubscribe_token', $token)->first();

        if (!$subscription) {
            return back()->with('error', 'Subscriere nu a fost găsită.');
        }

        $validator = Validator::make($request->all(), [
            'keywords' => 'nullable|string',
            'chambers' => 'nullable|array',
            'chambers.*' => 'in:cdep,senat',
            'statuses' => 'nullable|array',
            'statuses.*' => 'string',
            'urgent_only' => 'nullable|boolean',
            'risk_level' => 'nullable|in:low,medium,high,critical',
            'frequency' => 'required|in:instant,daily,weekly',
            'include_ai_summary' => 'nullable|boolean',
            'preferred_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Parse keywords
        $keywords = null;
        if ($request->filled('keywords')) {
            $keywords = array_map('trim', explode(',', $request->keywords));
            $keywords = array_filter($keywords);
        }

        // Update subscription
        $subscription->update([
            'keywords' => $keywords,
            'chambers' => $request->chambers,
            'statuses' => $request->statuses,
            'urgent_only' => $request->boolean('urgent_only'),
            'risk_level' => $request->risk_level,
            'frequency' => $request->frequency,
            'include_ai_summary' => $request->boolean('include_ai_summary', true),
            'preferred_time' => $request->preferred_time ?? '09:00:00',
            'active' => true, // Reactivate if was deactivated
        ]);

        return back()->with('success', 'Preferințele au fost actualizate cu succes!');
    }

    /**
     * Unsubscribe (deactivate subscription)
     */
    public function unsubscribe($token)
    {
        $subscription = BillSubscription::where('unsubscribe_token', $token)->first();

        if (!$subscription) {
            return view('subscriptions.error', [
                'message' => 'Subscriere nu a fost găsită.'
            ]);
        }

        if (!$subscription->active) {
            return view('subscriptions.error', [
                'message' => 'Această subscriere este deja dezactivată.'
            ]);
        }

        $subscription->deactivate();

        return view('subscriptions.unsubscribed', compact('subscription'));
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(Request $request, $token)
    {
        $subscription = BillSubscription::where('unsubscribe_token', $token)->first();

        if (!$subscription) {
            return back()->with('error', 'Subscriere nu a fost găsită.');
        }

        $subscription->update(['active' => true]);

        return back()->with('success', 'Subscriere reactivată cu succes!');
    }
}
