<?php

namespace App\Console\Commands;

use App\Models\BillAnalysis;
use App\Models\LegislativeBill;
use App\Services\AI\OpenRouterService;
use Exception;
use Illuminate\Console\Command;

class AssessBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assess:bills
                            {--limit= : Maximum number of bills to assess}
                            {--force : Re-assess bills that were already analyzed}
                            {--bill= : Assess a specific bill by ID}
                            {--test : Test API connection only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assess legislative bills using OpenRouter AI';

    protected $openRouter;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Test mode - just check API connection
        if ($this->option('test')) {
            return $this->testConnection();
        }

        $this->info('=================================');
        $this->info('Legislative Bill AI Assessment');
        $this->info('=================================');
        $this->newLine();

        try {
            $this->openRouter = new OpenRouterService;
        } catch (Exception $e) {
            $this->error('Failed to initialize OpenRouter service: '.$e->getMessage());
            $this->newLine();
            $this->warn('Make sure to set OPENROUTER_API_KEY in your .env file');
            $this->warn('Get your API key from: https://openrouter.ai/keys');

            return 1;
        }

        // Get bills to assess
        $query = LegislativeBill::query();

        if ($billId = $this->option('bill')) {
            // Assess specific bill
            $query->where('id', $billId);
        } elseif (! $this->option('force')) {
            // Only unanalyzed bills
            $query->where(function ($q) {
                $q->where('analyzed', false)
                    ->orWhereNull('analyzed');
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit($limit);
        }

        $bills = $query->get();

        if ($bills->isEmpty()) {
            $this->warn('No bills found to assess.');
            $this->info('Try running: php artisan scrape:bills --limit=10 first');

            return 0;
        }

        $this->info("Found {$bills->count()} bills to assess");
        $this->newLine();

        // Process bills
        $bar = $this->output->createProgressBar($bills->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $stats = [
            'total' => $bills->count(),
            'success' => 0,
            'failed' => 0,
            'total_tokens' => 0,
            'total_cost' => 0,
            'total_time' => 0,
        ];

        foreach ($bills as $bill) {
            $bar->setMessage("Assessing Bill #{$bill->id}: ".substr($bill->title, 0, 40).'...');

            try {
                $result = $this->assessBill($bill);

                if ($result['success']) {
                    $stats['success']++;
                    $stats['total_tokens'] += $result['metadata']['token_count'];
                    $stats['total_cost'] += $result['metadata']['cost'];
                    $stats['total_time'] += $result['metadata']['processing_time_ms'];
                } else {
                    $stats['failed']++;
                    $this->newLine();
                    $this->error("Failed to assess Bill #{$bill->id}: ".($result['error'] ?? 'Unknown error'));
                }

                $bar->advance();

                // Rate limiting - wait 1 second between requests
                sleep(1);

            } catch (Exception $e) {
                $stats['failed']++;
                $this->newLine();
                $this->error("Error assessing Bill #{$bill->id}: ".$e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Display statistics
        $this->displayStats($stats);

        return $stats['failed'] === 0 ? 0 : 1;
    }

    /**
     * Assess a single bill
     */
    protected function assessBill(LegislativeBill $bill): array
    {
        // Call OpenRouter AI
        $result = $this->openRouter->analyzeBill(
            $bill->title,
            $bill->description,
            $bill->full_text
        );

        if (! $result['success']) {
            return $result;
        }

        // Save analysis to database
        $analysis = BillAnalysis::create([
            'bill_id' => $bill->id,
            'analysis_type' => 'ai_assessment',
            'analysis_result' => $result['analysis'],
            'confidence_score' => $result['analysis']['confidence_score'] ?? null,
            'token_count' => $result['metadata']['token_count'],
            'analysis_cost' => $result['metadata']['cost'],
            'model_version' => $result['metadata']['model'],
            'prompt_version' => '1.0',
            'processing_time_ms' => $result['metadata']['processing_time_ms'],
            'analyzed_at' => now(),
        ]);

        // Update bill's analyzed flag
        $bill->update([
            'analyzed' => true,
            'analyzed_at' => now(),
        ]);

        return $result;
    }

    /**
     * Test OpenRouter API connection
     */
    protected function testConnection(): int
    {
        $this->info('Testing OpenRouter API connection...');
        $this->newLine();

        try {
            $openRouter = new OpenRouterService;
            $result = $openRouter->testConnection();

            if ($result['success']) {
                $this->info('✓ '.$result['message']);
                if (isset($result['models_available'])) {
                    $this->info("✓ {$result['models_available']} models available");
                }
                $this->newLine();
                $this->info('OpenRouter is configured correctly!');

                return 0;
            } else {
                $this->error('✗ '.$result['message']);

                return 1;
            }

        } catch (Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());
            $this->newLine();
            $this->warn('Make sure to set OPENROUTER_API_KEY in your .env file');
            $this->warn('Get your API key from: https://openrouter.ai/keys');

            return 1;
        }
    }

    /**
     * Display assessment statistics
     */
    protected function displayStats(array $stats): void
    {
        $this->info('=================================');
        $this->info('ASSESSMENT SUMMARY');
        $this->info('=================================');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bills', $stats['total']],
                ['Successfully Assessed', $stats['success']],
                ['Failed', $stats['failed']],
                ['Total Tokens Used', number_format($stats['total_tokens'])],
                ['Total Cost', '$'.number_format($stats['total_cost'], 4)],
                ['Average Time per Bill', round($stats['total_time'] / max($stats['total'], 1)).'ms'],
                ['Total Processing Time', round($stats['total_time'] / 1000, 2).'s'],
            ]
        );

        $this->newLine();

        if ($stats['success'] > 0) {
            $this->info('✓ Bill assessments have been saved to the database');
            $this->info('✓ You can view them on the dashboard or bills list');
        }

        if ($stats['failed'] > 0) {
            $this->warn("⚠ {$stats['failed']} bills failed to be assessed. Check logs for details.");
        }
    }
}
