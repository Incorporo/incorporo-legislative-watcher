<?php

namespace App\Console\Commands;

use App\Models\BillDocument;
use App\Models\ScrapingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:documents
                            {--limit=50 : Maximum documents to download}
                            {--force : Re-download existing documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download PDF documents for legislative bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info('Downloading legislative documents...');

        // Create job
        $job = ScrapingJob::create([
            'job_type' => 'documents',
            'chamber' => 'both',
            'trigger' => 'cron',
        ]);

        $job->markAsStarted();

        try {
            // Find documents that need downloading
            $query = BillDocument::where('downloaded', false)
                ->orWhere(function ($q) use ($force) {
                    if ($force) {
                        $q->where('downloaded', true);
                    }
                });

            $documents = $query->limit($limit)->get();

            $job->items_total = $documents->count();
            $job->save();

            if ($documents->isEmpty()) {
                $this->info('✅ No documents to download');
                $job->markAsCompleted();

                return 0;
            }

            $this->info("Found {$documents->count()} documents to download");

            $bar = $this->output->createProgressBar($documents->count());

            foreach ($documents as $document) {
                try {
                    $this->downloadDocument($document, $job);
                    $bar->advance();

                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Failed to download {$document->title}: ".$e->getMessage());

                    $document->download_attempts++;
                    $document->download_error = $e->getMessage();
                    $document->save();

                    $job->items_failed++;
                    $job->errors_count++;
                }

                // Rate limiting
                sleep(2);
            }

            $bar->finish();
            $this->newLine();

            $job->markAsCompleted();

            $this->info('✅ Document download completed');
            $this->info('  Downloaded: '.($job->items_processed - $job->items_failed).", Failed: {$job->items_failed}");
            $this->info('  Total bytes: '.$this->formatBytes($job->bytes_downloaded));

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Download a single document
     */
    protected function downloadDocument(BillDocument $document, ScrapingJob $job)
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; RomanianLegislativeWatcher/1.0)',
            ])
            ->get($document->url);

        if (! $response->successful()) {
            throw new \Exception("HTTP {$response->status()} error");
        }

        $content = $response->body();
        $size = strlen($content);

        // Generate filename
        $hash = hash('sha256', $document->url);
        $extension = pathinfo(parse_url($document->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'pdf';
        $billId = $document->bill_id;
        $filename = "bills/{$billId}/{$document->document_type}_{$hash}.{$extension}";

        // Store file
        Storage::put($filename, $content);

        // Update document record
        $document->update([
            'downloaded' => true,
            'downloaded_at' => now(),
            'local_path' => $filename,
            'file_size' => $size,
            'file_hash' => hash('sha256', $content),
        ]);

        // Update job stats
        $job->items_processed++;
        $job->bytes_downloaded += $size;
        $job->http_requests++;
        $job->save();

        Log::info("Downloaded document: {$document->title} ({$this->formatBytes($size)})");
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }
}
