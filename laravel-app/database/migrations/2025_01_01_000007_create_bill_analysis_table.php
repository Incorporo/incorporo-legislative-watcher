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
        Schema::create('bill_analysis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');

            // Analysis type and result
            $table->string('analysis_type', 50)->index(); // summary, risk_assessment, impact_analysis, trend
            $table->json('analysis_result'); // Structured AI output

            // Quality metrics
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0.00 to 1.00
            $table->integer('token_count')->nullable(); // Tokens used
            $table->decimal('analysis_cost', 8, 4)->nullable(); // Cost in USD

            // AI model information
            $table->string('model_version', 50)->nullable(); // e.g., "gpt-4", "claude-3-opus"
            $table->string('prompt_version', 50)->nullable(); // Track prompt iterations

            // Processing metadata
            $table->integer('processing_time_ms')->nullable(); // Milliseconds
            $table->timestamp('analyzed_at')->index();

            // Review and validation
            $table->boolean('human_reviewed')->default(false);
            $table->boolean('approved')->nullable(); // null = pending, true = approved, false = rejected
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'analysis_type']);
            $table->index(['analysis_type', 'analyzed_at']);
            $table->index(['human_reviewed', 'approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_analysis');
    }
};
