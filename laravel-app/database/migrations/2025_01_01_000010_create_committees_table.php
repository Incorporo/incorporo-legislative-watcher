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
        Schema::create('committees', function (Blueprint $table) {
            $table->id();

            // Committee identification
            $table->string('internal_id', 50)->nullable();
            $table->string('name', 255)->index();
            $table->string('name_short', 100)->nullable();
            $table->enum('chamber', ['cdep', 'senate', 'joint'])->index();

            // Committee details
            $table->string('type', 100)->nullable(); // permanent, special, investigation
            $table->text('description')->nullable();
            $table->text('jurisdiction')->nullable(); // Areas of responsibility

            // Leadership
            $table->foreignId('chair_id')->nullable()->constrained('legislators')->onDelete('set null');
            $table->json('leadership')->nullable(); // Array of vice-chairs, secretaries

            // Status
            $table->boolean('active')->default(true)->index();
            $table->date('established_date')->nullable();
            $table->date('dissolved_date')->nullable();

            // Contact
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('website_url')->nullable();

            // Statistics
            $table->integer('member_count')->default(0);
            $table->integer('bills_reviewed')->default(0);
            $table->integer('meetings_held')->default(0);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['chamber', 'internal_id']);
            $table->index(['chamber', 'active']);
        });

        // Pivot table for committee members
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_id')->constrained('committees')->onDelete('cascade');
            $table->foreignId('legislator_id')->constrained('legislators')->onDelete('cascade');

            $table->enum('role', ['member', 'chair', 'vice_chair', 'secretary'])->default('member');
            $table->date('joined_date')->nullable();
            $table->date('left_date')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Indexes
            $table->unique(['committee_id', 'legislator_id', 'active']);
            $table->index(['legislator_id', 'active']);
        });

        // Pivot table for bills assigned to committees
        Schema::create('committee_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_id')->constrained('committees')->onDelete('cascade');
            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');

            $table->date('assigned_date')->index();
            $table->date('review_deadline')->nullable()->index();
            $table->date('report_date')->nullable();
            $table->enum('status', ['assigned', 'under_review', 'reported', 'deferred'])->default('assigned')->index();
            $table->enum('recommendation', ['favorable', 'unfavorable', 'amended', 'none'])->nullable();

            $table->text('report_summary')->nullable();
            $table->text('report_url')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'status']);
            $table->index(['committee_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_assignments');
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('committees');
    }
};
