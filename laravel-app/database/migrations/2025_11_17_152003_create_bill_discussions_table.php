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
        Schema::create('bill_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade'); // Null = public discussion
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Discussion creator
            $table->string('title');
            $table->text('content');
            $table->string('discussion_type')->default('general'); // general, amendment, impact, strategy
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('bill_id');
            $table->index('team_id');
            $table->index('user_id');
            $table->index(['bill_id', 'team_id']);
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_discussions');
    }
};
