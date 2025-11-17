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
        Schema::create('legislative_bills', function (Blueprint $table) {
            $table->id();

            // Chamber and identification
            $table->enum('chamber', ['cdep', 'senate'])->index();
            $table->string('bill_number', 50)->index();
            $table->integer('year')->index();
            $table->string('internal_id', 50)->index(); // idp for CDEP, cod for Senate

            // Bill metadata
            $table->text('title');
            $table->string('type', 100)->nullable(); // law, legislative proposal, emergency ordinance, etc.
            $table->string('status', 100)->nullable()->index();
            $table->boolean('urgency_status')->default(false)->index();
            $table->string('first_chamber', 50)->nullable();
            $table->string('decision_chamber', 50)->nullable();
            $table->date('registration_date')->nullable()->index();

            // Content tracking
            $table->text('description')->nullable();
            $table->longText('full_text')->nullable(); // Extracted from PDFs
            $table->string('content_hash', 64)->nullable(); // SHA256 for change detection

            // Source and URLs
            $table->text('url');
            $table->text('source_url')->nullable(); // Original source if different

            // Additional metadata (JSON for flexibility)
            $table->json('metadata')->nullable(); // Stores chamber-specific data

            // Tracking
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamp('last_changed_at')->nullable();
            $table->integer('scrape_count')->default(0);
            $table->integer('change_count')->default(0);

            // AI Analysis status
            $table->boolean('analyzed')->default(false)->index();
            $table->timestamp('analyzed_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Allow soft deletion

            // Indexes
            $table->unique(['chamber', 'internal_id']);
            $table->index(['chamber', 'year']);
            $table->index(['status', 'urgency_status']);
            $table->index('last_scraped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legislative_bills');
    }
};
