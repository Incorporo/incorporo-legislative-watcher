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
        Schema::table('legislative_bills', function (Blueprint $table) {
            // AI Assessment Status
            $table->boolean('ai_assessed')->default(false)->after('summary');
            $table->timestamp('ai_assessed_at')->nullable()->after('ai_assessed');
            $table->integer('ai_assessment_priority')->default(50)->after('ai_assessed_at'); // 0-100 score
            $table->string('ai_assessment_status')->default('pending')->after('ai_assessment_priority'); // pending, processing, completed, failed
            $table->text('ai_assessment_error')->nullable()->after('ai_assessment_status');

            // Advanced AI Features
            $table->json('stakeholder_impact')->nullable()->after('ai_assessment_error');
            $table->json('conflict_analysis')->nullable()->after('stakeholder_impact');
            $table->json('voting_predictions')->nullable()->after('conflict_analysis');
            $table->json('policy_recommendations')->nullable()->after('voting_predictions');
            $table->text('ai_summary')->nullable()->after('policy_recommendations');

            // Batch Processing
            $table->integer('batch_assessment_attempts')->default(0)->after('ai_summary');
            $table->timestamp('last_assessment_attempt')->nullable()->after('batch_assessment_attempts');

            // Indexes for performance
            $table->index('ai_assessed');
            $table->index('ai_assessment_status');
            $table->index(['ai_assessed', 'ai_assessment_priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legislative_bills', function (Blueprint $table) {
            $table->dropIndex(['legislative_bills_ai_assessed_index']);
            $table->dropIndex(['legislative_bills_ai_assessment_status_index']);
            $table->dropIndex(['legislative_bills_ai_assessed_ai_assessment_priority_index']);

            $table->dropColumn([
                'ai_assessed',
                'ai_assessed_at',
                'ai_assessment_priority',
                'ai_assessment_status',
                'ai_assessment_error',
                'stakeholder_impact',
                'conflict_analysis',
                'voting_predictions',
                'policy_recommendations',
                'ai_summary',
                'batch_assessment_attempts',
                'last_assessment_attempt',
            ]);
        });
    }
};
