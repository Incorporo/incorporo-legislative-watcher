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
        Schema::create('bill_committees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->foreignId('timeline_event_id')->nullable()->constrained('bill_timeline')->onDelete('set null');

            // Committee identification
            $table->string('committee_name', 255)->index();
            $table->string('committee_id', 50)->nullable()->comment('CDEP committee ID from URL (idc parameter)');
            $table->text('committee_link')->nullable();
            $table->string('chamber', 50)->nullable();
            $table->string('legislature', 50)->nullable()->comment('Legislative session (leg parameter)');

            // Assignment type
            $table->enum('assignment_type', ['raport', 'aviz'])->index()
                ->comment('raport = main report committee, aviz = advisory opinion');

            // Response tracking
            $table->boolean('report_received')->default(false)->index();
            $table->date('report_date')->nullable();
            $table->string('report_number', 50)->nullable();
            $table->text('report_url')->nullable();
            $table->string('report_result', 100)->nullable()
                ->comment('favorabil, nefavorabil, cu amendamente');

            // Deadlines from CDEP timeline
            $table->date('deadline_amendments')->nullable()->index();
            $table->date('deadline_report')->nullable()->index();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['bill_id', 'assignment_type']);
            $table->index(['committee_name', 'chamber']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_committees');
    }
};
