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
        Schema::table('bill_documents', function (Blueprint $table) {
            // Optional link to specific timeline event
            // This allows us to track which documents are associated with which timeline event
            // (e.g., committee report document linked to "primire raport" event)
            $table->foreignId('timeline_event_id')
                ->after('bill_id')
                ->nullable()
                ->constrained('bill_timeline')
                ->onDelete('set null')
                ->comment('Optional link to the timeline event where this document appeared');

            // Add index for querying documents by timeline event
            $table->index('timeline_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_documents', function (Blueprint $table) {
            $table->dropForeign(['timeline_event_id']);
            $table->dropColumn('timeline_event_id');
        });
    }
};
