<?php

namespace App\Jobs;

use App\Models\BillSubscription;
use App\Mail\BillDigest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBillDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BillSubscription $subscription;

    /**
     * Create a new job instance.
     */
    public function __construct(BillSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get matching bills for this subscription
        $bills = $this->subscription->getMatchingBills(50);

        // Skip if no bills match
        if ($bills->isEmpty()) {
            Log::info('No bills to send for subscription', [
                'subscription_id' => $this->subscription->id,
                'email' => $this->subscription->email
            ]);
            return;
        }

        // Send email
        try {
            Mail::to($this->subscription->email)->send(
                new BillDigest($this->subscription, $bills, $bills->count())
            );

            // Mark as notified
            $this->subscription->markAsNotified();

            Log::info('Bill digest sent successfully', [
                'subscription_id' => $this->subscription->id,
                'email' => $this->subscription->email,
                'bills_count' => $bills->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send bill digest', [
                'subscription_id' => $this->subscription->id,
                'email' => $this->subscription->email,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bill digest job failed permanently', [
            'subscription_id' => $this->subscription->id,
            'email' => $this->subscription->email,
            'error' => $exception->getMessage()
        ]);
    }
}
