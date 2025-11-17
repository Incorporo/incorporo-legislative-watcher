<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scraping_jobs', function (Blueprint $table) {
            $table->id();

            // Job configuration
            $table->string('job_type', 50)->index(); // full_sync, incremental, single_bill, documents
            $table->enum('chamber', ['cdep', 'senate', 'both'])->index();
            $table->string('scope', 100)->nullable(); // e.g., "year:2025", "bill_id:12345"

            // Status tracking
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending')->index();
            $table->integer('progress')->default(0); // Percentage 0-100

            // Metrics
            $table->integer('items_total')->default(0);
            $table->integer('items_processed')->default(0);
            $table->integer('items_created')->default(0);
            $table->integer('items_updated')->default(0);
            $table->integer('items_failed')->default(0);
            $table->integer('errors_count')->default(0);

            // Timing
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Error tracking
            $table->text('error_log')->nullable();
            $table->json('error_summary')->nullable(); // Categorized errors

            // Resource usage
            $table->integer('http_requests')->default(0);
            $table->integer('bytes_downloaded')->default(0);
            $table->decimal('estimated_cost', 8, 4)->default(0); // API costs if any

            // Trigger information
            $table->enum('trigger', ['cron', 'manual', 'api', 'system'])->default('cron');
            $table->foreignId('triggered_by')->nullable(); // User ID if manual

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['job_type', 'status']);
            $table->index(['chamber', 'started_at']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
