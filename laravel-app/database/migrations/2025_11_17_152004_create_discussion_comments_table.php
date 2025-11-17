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
        Schema::create('discussion_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('bill_discussions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_comments')->onDelete('cascade'); // For threaded replies
            $table->text('content');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->integer('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('discussion_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index(['discussion_id', 'created_at']);
        });

        // Table for tracking comment likes
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('discussion_comments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']); // User can only like once
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('discussion_comments');
    }
};
