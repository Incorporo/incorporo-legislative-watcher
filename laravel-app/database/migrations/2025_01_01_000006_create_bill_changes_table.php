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
        Schema::create('bill_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');

            // Change tracking
            $table->string('field_name', 100)->index(); // Which field changed
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            // Change classification
            $table->enum('change_type', ['status', 'content', 'metadata', 'document', 'timeline'])->index();
            $table->enum('importance', ['low', 'medium', 'high', 'critical'])->default('medium')->index();

            // Detection metadata
            $table->timestamp('detected_at')->index();
            $table->string('detection_method', 50)->default('scraper'); // scraper, manual, ai

            // Notification tracking
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'detected_at']);
            $table->index(['change_type', 'importance']);
            $table->index('notification_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_changes');
    }
};
