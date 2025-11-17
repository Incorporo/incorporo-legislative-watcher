<?php

namespace App\Console\Commands;

use App\Models\LegislativeBill;
use App\Services\AIAssessmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoAssessBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:auto-assess
                            {--limit=10 : Maximum number of bills to assess in this run}
                            {--force : Force reassessment of already assessed bills}
                            {--priority= : Minimum priority level (0-100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assess bills using AI (runs daily via scheduler)';

    protected $aiService;

    public function __construct(AIAssessmentService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting batch AI assessment...');

        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $minPriority = $this->option('priority') ?? 30;

        // Get bills that need assessment
        $query = LegislativeBill::query();

        if (! $force) {
            // Only assess unassessed bills
            $query->where(function ($q) {
                $q->where('ai_assessed', false)
                    ->orWhere('ai_assessment_status', 'failed');
            });
        }

        // Prioritize by importance
        $query->where('ai_assessment_priority', '>=', $minPriority)
            ->orderBy('ai_assessment_priority', 'desc')
            ->orderBy('registration_date', 'desc')
            ->limit($limit);

        $bills = $query->get();

        if ($bills->isEmpty()) {
            $this->info('No bills to assess.');

            return 0;
        }

        $this->info("Found {$bills->count()} bills to assess.");

        $bar = $this->output->createProgressBar($bills->count());
        $bar->start();

        $successful = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($bills as $bill) {
            try {
                // Check if we should skip this bill (rate limiting, cost management)
                if ($this->shouldSkipBill($bill)) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                // Mark as processing
                $bill->update([
                    'ai_assessment_status' => 'processing',
                    'last_assessment_attempt' => now(),
                ]);

                // Run comprehensive AI assessment
                $assessment = $this->aiService->assessBillComprehensive($bill);

                if ($assessment) {
                    // Update bill with assessment data
                    $bill->update([
                        'ai_assessed' => true,
                        'ai_assessed_at' => now(),
                        'ai_assessment_status' => 'completed',
                        'ai_assessment_error' => null,
                        'stakeholder_impact' => $assessment['stakeholder_impact'] ?? null,
                        'conflict_analysis' => $assessment['conflict_analysis'] ?? null,
                        'voting_predictions' => $assessment['voting_predictions'] ?? null,
                        'policy_recommendations' => $assessment['policy_recommendations'] ?? null,
                        'ai_summary' => $assessment['summary'] ?? null,
                    ]);

                    $successful++;
                } else {
                    throw new \Exception('Assessment service returned null');
                }

            } catch (\Exception $e) {
                $bill->update([
                    'ai_assessment_status' => 'failed',
                    'ai_assessment_error' => $e->getMessage(),
                    'batch_assessment_attempts' => $bill->batch_assessment_attempts + 1,
                ]);

                Log::error("Failed to assess bill {$bill->id}: ".$e->getMessage());
                $failed++;
            }

            $bar->advance();

            // Rate limiting: wait between requests
            usleep(500000); // 0.5 second delay
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Assessment complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successful],
                ['Failed', $failed],
                ['Skipped', $skipped],
            ]
        );

        if ($failed > 0) {
            $this->warn("$failed bills failed assessment. Check logs for details.");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Determine if a bill should be skipped based on various criteria
     */
    protected function shouldSkipBill(LegislativeBill $bill): bool
    {
        // Skip if too many failed attempts
        if ($bill->batch_assessment_attempts >= 3) {
            return true;
        }

        // Skip if attempted recently (within last hour)
        if ($bill->last_assessment_attempt &&
            $bill->last_assessment_attempt->diffInHours(now()) < 1) {
            return true;
        }

        return false;
    }
}
