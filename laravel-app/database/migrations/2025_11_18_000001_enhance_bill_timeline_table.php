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
        Schema::table('bill_timeline', function (Blueprint $table) {
            // Sequencing fields for CDEP timeline
            $table->integer('sequence_order')->after('bill_id')->nullable()->index();
            $table->integer('chamber_round')->after('chamber')->default(1)
                ->comment('Which round in this chamber (for re-examination)');

            // Enhanced status tracking
            $table->boolean('is_adoption')->after('vote_result')->default(false)
                ->comment('Is this an adoption/vote event?');
            $table->boolean('is_final')->after('is_adoption')->default(false)
                ->comment('Is this the final event (publication)?');

            // Vote details as JSON (more flexible than individual columns)
            $table->json('vote_details')->after('vote_result')->nullable()
                ->comment('Detailed voting info: quorum, constitutional requirements, etc.');

            // Deadline tracking (enhanced from single deadline field)
            $table->string('deadline_type', 100)->after('deadline')->nullable()
                ->comment('Type of deadline: termen depunere amendamente, termen depunere raport');

            // Media links from CDEP
            $table->text('stenogram_link')->after('source_url')->nullable()
                ->comment('Link to stenogram (debate transcript)');
            $table->text('video_link')->after('stenogram_link')->nullable()
                ->comment('Link to video recording');

            // Committees and documents as JSON (flexible structure)
            $table->json('committees')->after('committee')->nullable()
                ->comment('Array of committees with links and IDs');
            $table->json('documents')->after('committees')->nullable()
                ->comment('Array of documents attached to this timeline event');

            // Add composite index for timeline queries
            $table->index(['bill_id', 'sequence_order'], 'idx_bill_sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_timeline', function (Blueprint $table) {
            $table->dropColumn([
                'sequence_order',
                'chamber_round',
                'is_adoption',
                'is_final',
                'vote_details',
                'deadline_type',
                'stenogram_link',
                'video_link',
                'committees',
                'documents',
            ]);

            $table->dropIndex('idx_bill_sequence');
        });
    }
};
