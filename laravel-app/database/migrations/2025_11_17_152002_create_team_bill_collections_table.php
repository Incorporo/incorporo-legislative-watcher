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
        Schema::create('team_bill_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('color')->default('#3b82f6'); // Collection color tag
            $table->integer('bill_count')->default(0);
            $table->boolean('is_shared')->default(true); // Shared with team or private
            $table->timestamps();

            $table->index('team_id');
            $table->index('created_by');
            $table->index(['team_id', 'is_shared']);
        });

        // Pivot table for bills in collections
        Schema::create('collection_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('team_bill_collections')->onDelete('cascade');
            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->text('note')->nullable(); // Why this bill was added
            $table->integer('position')->default(0); // For ordering
            $table->timestamps();

            $table->unique(['collection_id', 'bill_id']);
            $table->index('bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_bills');
        Schema::dropIfExists('team_bill_collections');
    }
};
