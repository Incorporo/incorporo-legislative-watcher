<?php

namespace App\Console\Commands;

use App\Models\ScrapingJob;
use App\Services\Scrapers\CDEPScraper;
use App\Services\Scrapers\SenateScraper;
use Illuminate\Console\Command;

class ScrapeBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:bills
                            {--chamber=all : Chamber to scrape (cdep, senate, all)}
                            {--year= : Filter by year}
                            {--limit= : Maximum number of bills to scrape}
                            {--full : Scrape all bills including details}
                            {--force : Force rescraping even if recently scraped}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape legislative bills from Romanian Parliament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chamber = $this->option('chamber');
        $year = $this->option('year');
        $limit = $this->option('limit');
        $full = $this->option('full');
        $force = $this->option('force');

        $this->info('Starting legislative bill scraping...');
        $this->info("Chamber: {$chamber}, Year: ".($year ?: 'all').', Limit: '.($limit ?: 'none'));

        // Create scraping job record
        $job = ScrapingJob::create([
            'job_type' => $full ? 'full_sync' : 'incremental',
            'chamber' => $chamber,
            'scope' => $year ? "year:{$year}" : 'all',
            'trigger' => 'manual',
        ]);

        $job->markAsStarted();

        try {
            // Scrape CDEP
            if (in_array($chamber, ['all', 'cdep'])) {
                $this->info("\n📖 Scraping CDEP (Chamber of Deputies)...");
                $this->scrapeChamber('cdep', $year, $limit, $full, $force, $job);
            }

            // Scrape Senate
            if (in_array($chamber, ['all', 'senate'])) {
                $this->info("\n📖 Scraping Senate...");
                $this->scrapeChamber('senate', $year, $limit, $full, $force, $job);
            }

            $job->markAsCompleted();

            $this->newLine();
            $this->info('✅ Scraping completed successfully!');
            $this->displayStats($job);

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());

            $this->error('❌ Scraping failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }

    /**
     * Scrape a specific chamber
     */
    protected function scrapeChamber($chamber, $year, $limit, $full, $force, ScrapingJob $job)
    {
        $scraper = $chamber === 'cdep' ? new CDEPScraper : new SenateScraper;

        // Step 1: Get list of bills
        $this->info('Fetching bill list...');
        $bills = $scraper->scrapeBillList($chamber === 'cdep' ? 2 : null, $year, $limit);

        $job->items_total += count($bills);
        $job->save();

        $this->info('Found '.count($bills).' bills');

        if (empty($bills)) {
            $this->warn("No bills found for {$chamber}");

            return;
        }

        // Step 2: Process each bill
        $bar = $this->output->createProgressBar(count($bills));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($bills as $billData) {
            $internalId = $billData['internal_id'];
            $bar->setMessage("Processing {$chamber} bill {$internalId}");

            try {
                if ($full) {
                    // Scrape full details
                    $details = $scraper->scrapeBillDetail($internalId, $chamber === 'cdep' ? 2 : null);
                    $scraper->saveBill($details, $job);
                } else {
                    // Just save basic info from list
                    $billData['chamber'] = $chamber;
                    $billData['last_scraped_at'] = now();
                    $scraper->saveBill($billData, $job);
                }

                $job->http_requests++;

                $bar->advance();

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to scrape bill {$internalId}: ".$e->getMessage());
                $job->items_failed++;
                $job->errors_count++;

                continue;
            }
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Display scraping statistics
     */
    protected function displayStats(ScrapingJob $job)
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Items Total', $job->items_total],
                ['Items Processed', $job->items_processed],
                ['Items Created', $job->items_created],
                ['Items Updated', $job->items_updated],
                ['Items Failed', $job->items_failed],
                ['HTTP Requests', $job->http_requests],
                ['Duration', $job->duration_seconds.'s'],
            ]
        );
    }
}
