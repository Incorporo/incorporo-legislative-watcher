<?php

namespace App\Console\Commands;

use App\Models\BillSubscription;
use App\Jobs\SendBillDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBillNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send
                            {--frequency= : Send only specific frequency (instant, daily, weekly)}
                            {--test : Test mode - show subscriptions without sending}
                            {--limit= : Limit number of subscriptions to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send bill notifications to active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📧 Starting bill notifications...');

        // Build query for subscriptions due for notification
        $query = BillSubscription::dueForNotification();

        // Filter by frequency if specified
        if ($frequency = $this->option('frequency')) {
            if (!in_array($frequency, ['instant', 'daily', 'weekly'])) {
                $this->error('Invalid frequency. Use: instant, daily, or weekly');
                return 1;
            }
            $query->where('frequency', $frequency);
            $this->info("Filtering for frequency: {$frequency}");
        }

        // Apply limit if specified
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        // Get subscriptions
        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->warn('No subscriptions due for notification.');
            return 0;
        }

        $this->info("Found {$subscriptions->count()} subscription(s) to process");

        if ($this->option('test')) {
            $this->warn('🧪 TEST MODE - No emails will be sent');
            $this->table(
                ['ID', 'Email', 'Frequency', 'Last Notified', 'Filters'],
                $subscriptions->map(function ($sub) {
                    return [
                        $sub->id,
                        $sub->email,
                        $sub->getFrequencyLabel(),
                        $sub->last_notified_at ? $sub->last_notified_at->diffForHumans() : 'Never',
                        $sub->getSummary()
                    ];
                })->toArray()
            );
            return 0;
        }

        // Process subscriptions
        $bar = $this->output->createProgressBar($subscriptions->count());
        $bar->start();

        $sent = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Dispatch job to send digest
                SendBillDigest::dispatch($subscription);
                $sent++;
            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to dispatch bill digest job', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Dispatched {$sent} notification job(s)");
        if ($errors > 0) {
            $this->error("❌ Failed to dispatch {$errors} job(s)");
        }

        return $errors > 0 ? 1 : 0;
    }
}
