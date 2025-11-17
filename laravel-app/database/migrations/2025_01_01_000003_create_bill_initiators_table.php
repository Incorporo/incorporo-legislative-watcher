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
        Schema::create('bill_initiators', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');
            $table->foreignId('legislator_id')->nullable()->constrained('legislators')->onDelete('set null');

            // Initiator details
            $table->string('name', 255); // Name as appears on bill
            $table->enum('type', ['mp', 'government', 'citizens', 'president', 'other'])->index();
            $table->string('party', 100)->nullable();
            $table->enum('chamber', ['cdep', 'senate', 'both'])->nullable();

            // Role in initiation
            $table->enum('role', ['primary', 'co_sponsor', 'supporter'])->default('co_sponsor');
            $table->integer('position')->default(0); // Order of sponsors

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'type']);
            $table->index(['legislator_id', 'type']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_initiators');
    }
};
