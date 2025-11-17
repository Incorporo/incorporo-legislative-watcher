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
        Schema::create('user_watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->boolean('notifications_enabled')->default(true);
            $table->string('priority')->default('normal'); // low, normal, high
            $table->text('personal_note')->nullable();
            $table->timestamps();

            // Ensure user can only watch a bill once
            $table->unique(['user_id', 'bill_id']);

            // Index for faster queries
            $table->index(['user_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_watchlists');
    }
};
