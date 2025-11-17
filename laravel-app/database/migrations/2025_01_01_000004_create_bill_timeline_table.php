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
        Schema::create('bill_timeline', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');

            // Event details
            $table->date('event_date')->index();
            $table->string('event_type', 100)->index(); // registered, committee_review, vote, amended, etc.
            $table->text('description');
            $table->text('details')->nullable(); // Additional context

            // Committee/Chamber context
            $table->string('chamber', 50)->nullable();
            $table->string('committee', 100)->nullable();

            // Vote information (if applicable)
            $table->integer('votes_for')->nullable();
            $table->integer('votes_against')->nullable();
            $table->integer('votes_abstain')->nullable();
            $table->enum('vote_result', ['passed', 'rejected', 'pending'])->nullable();

            // Deadlines and expectations
            $table->date('deadline')->nullable(); // For review, opinions, etc.
            $table->boolean('deadline_met')->nullable();

            // Metadata for varying event types
            $table->json('metadata')->nullable();

            // Source tracking
            $table->text('source_url')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'event_date']);
            $table->index(['event_type', 'event_date']);
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_timeline');
    }
};
