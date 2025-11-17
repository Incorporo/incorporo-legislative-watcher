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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // owner, admin, member, viewer
            $table->json('permissions')->nullable(); // Specific permissions
            $table->timestamp('joined_at');
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'user_id']); // User can only be in team once
            $table->index('user_id');
            $table->index(['team_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
