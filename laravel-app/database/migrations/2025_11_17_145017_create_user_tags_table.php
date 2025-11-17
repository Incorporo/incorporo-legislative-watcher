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
        Schema::create('user_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3b82f6'); // Hex color
            $table->text('description')->nullable();
            $table->timestamps();

            // Ensure unique tag names per user
            $table->unique(['user_id', 'name']);
            $table->index('user_id');
        });

        // Pivot table for tags-to-bills relationship
        Schema::create('bill_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_tag_id')->constrained('user_tags')->onDelete('cascade');
            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_tag_id', 'bill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_tag');
        Schema::dropIfExists('user_tags');
    }
};
