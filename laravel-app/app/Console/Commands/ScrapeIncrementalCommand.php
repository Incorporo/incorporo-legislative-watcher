<?php

namespace App\Console\Commands;

use App\Models\LegislativeBill;
use App\Models\ScrapingJob;
use App\Services\Scrapers\CDEPScraper;
use App\Services\Scrapers\SenateScraper;
use Illuminate\Console\Command;

class ScrapeIncrementalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:incremental
                            {--chamber=all : Chamber to scrape (cdep, senate, all)}
                            {--hours=6 : Only scrape bills not scraped in last N hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incrementally scrape recently updated bills (suitable for CRON)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chamber = $this->option('chamber');
        $hours = (int) $this->option('hours');

        $this->info("Running incremental scrape (bills older than {$hours} hours)");

        // Create scraping job
        // Map 'all' to 'both' for database enum constraint
        $chamberValue = $chamber === 'all' ? 'both' : $chamber;

        $job = ScrapingJob::create([
            'job_type' => 'incremental',
            'chamber' => $chamberValue,
            'scope' => "incremental:{$hours}h",
            'trigger' => 'cron',
        ]);

        $job->markAsStarted();

        try {
            // Find bills that need updating
            $cutoff = now()->subHours($hours);

            $query = LegislativeBill::where(function ($q) use ($cutoff) {
                $q->whereNull('last_scraped_at')
                    ->orWhere('last_scraped_at', '<', $cutoff);
            });

            if ($chamber !== 'all') {
                $query->where('chamber', $chamber);
            }

            // Prioritize: urgent bills, recently changed, never scraped
            $bills = $query->orderByRaw('
                CASE
                    WHEN urgency_status = 1 THEN 1
                    WHEN last_changed_at > NOW() - INTERVAL 7 DAY THEN 2
                    WHEN last_scraped_at IS NULL THEN 3
                    ELSE 4
                END
            ')
                ->orderBy('last_scraped_at', 'asc')
                ->limit(100) // Process max 100 bills per run
                ->get();

            $job->items_total = $bills->count();
            $job->save();

            if ($bills->isEmpty()) {
                $this->info('✅ No bills need updating');
                $job->markAsCompleted();

                return 0;
            }

            $this->info("Found {$bills->count()} bills to update");

            $bar = $this->output->createProgressBar($bills->count());

            foreach ($bills as $bill) {
                $scraper = $bill->chamber === 'cdep' ? new CDEPScraper : new SenateScraper;

                try {
                    $details = $scraper->scrapeBillDetail(
                        $bill->internal_id,
                        $bill->chamber === 'cdep' ? 2 : null
                    );

                    $scraper->saveBill($details, $job);
                    $job->http_requests++;

                    $bar->advance();

                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Failed to update bill {$bill->internal_id}: ".$e->getMessage());
                    $job->items_failed++;
                    $job->errors_count++;
                }
            }

            $bar->finish();
            $this->newLine();

            $job->markAsCompleted();

            $this->info('✅ Incremental scrape completed');
            $this->info("  Created: {$job->items_created}, Updated: {$job->items_updated}, Failed: {$job->items_failed}");

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
