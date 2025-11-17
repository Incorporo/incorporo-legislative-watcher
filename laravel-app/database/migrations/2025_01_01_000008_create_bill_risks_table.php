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
        Schema::create('bill_risks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->foreignId('analysis_id')->nullable()->constrained('bill_analysis')->onDelete('set null');

            // Risk classification
            $table->string('risk_category', 100)->index(); // privacy, business, constitutional, democratic_process, etc.
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->index();
            $table->integer('risk_score')->nullable(); // 0-100 numerical score

            // Risk description
            $table->text('description');
            $table->text('justification'); // AI explanation or human reasoning
            $table->text('affected_parties')->nullable(); // Who is impacted
            $table->text('recommended_action')->nullable(); // What should be done

            // Evidence and references
            $table->json('evidence')->nullable(); // Links to specific sections, articles
            $table->json('related_risks')->nullable(); // IDs of related risk entries

            // Status and tracking
            $table->enum('status', ['active', 'resolved', 'monitoring', 'dismissed'])->default('active')->index();
            $table->timestamp('flagged_at')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            // Visibility and notifications
            $table->boolean('public')->default(true); // Show to public or internal only
            $table->boolean('alert_sent')->default(false);
            $table->timestamp('alert_sent_at')->nullable();

            // Validation
            $table->boolean('verified')->default(false); // Human verification
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable(); // User who verified

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'risk_level']);
            $table->index(['risk_category', 'risk_level']);
            $table->index(['status', 'flagged_at']);
            $table->index(['public', 'verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_risks');
    }
};
