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
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('filters'); // Store search parameters as JSON
            $table->boolean('is_default')->default(false);
            $table->integer('use_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
