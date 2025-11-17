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
        Schema::create('bill_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();

            // Subscription preferences
            $table->json('keywords')->nullable(); // Array of keywords to watch
            $table->json('chambers')->nullable(); // Array: ['cdep', 'senate']
            $table->json('categories')->nullable(); // Array of categories
            $table->json('statuses')->nullable(); // Array of bill statuses
            $table->boolean('urgent_only')->default(false);
            $table->string('risk_level')->nullable(); // Filter by risk level

            // Notification preferences
            $table->enum('frequency', ['instant', 'daily', 'weekly'])->default('daily');
            $table->boolean('include_ai_summary')->default(true);
            $table->time('preferred_time')->default('09:00:00'); // For daily/weekly digests

            // Status
            $table->boolean('active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->string('verification_token')->nullable();
            $table->string('unsubscribe_token')->unique();

            $table->timestamps();

            $table->index('email');
            $table->index('active');
            $table->index(['active', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_subscriptions');
    }
};
