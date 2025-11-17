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
        Schema::create('user_dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->json('widget_layout')->nullable(); // Array of widget configurations
            $table->json('visible_widgets')->nullable(); // Which widgets are enabled
            $table->string('theme')->default('light'); // light, dark, auto
            $table->json('chart_preferences')->nullable(); // Chart display preferences
            $table->timestamps();

            $table->index('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_preferences');
    }
};
