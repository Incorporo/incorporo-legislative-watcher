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
        Schema::create('legislators', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('internal_id', 50)->unique(); // MP ID from website
            $table->string('name', 255)->index();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();

            // Chamber and role
            $table->enum('chamber', ['cdep', 'senate', 'both'])->index();
            $table->string('party', 100)->nullable()->index();
            $table->string('party_normalized', 100)->nullable()->index(); // For consistent tracking
            $table->string('constituency', 100)->nullable(); // Electoral district

            // Legislature tracking
            $table->string('legislature', 50)->nullable(); // e.g., "2020-2024"
            $table->date('mandate_start')->nullable();
            $table->date('mandate_end')->nullable();
            $table->boolean('active')->default(true)->index();

            // Contact and profile
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('profile_url')->nullable();
            $table->text('photo_url')->nullable();

            // Biography and background
            $table->text('biography')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('education', 255)->nullable();
            $table->string('profession', 255)->nullable();

            // Committees
            $table->json('committees')->nullable(); // Array of committee memberships

            // Statistics (can be computed, but cached here)
            $table->integer('bills_initiated')->default(0);
            $table->integer('bills_co_sponsored')->default(0);
            $table->integer('questions_asked')->default(0);
            $table->integer('speeches_given')->default(0);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['party_normalized', 'active']);
            $table->index(['chamber', 'active']);
            $table->index('mandate_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legislators');
    }
};
