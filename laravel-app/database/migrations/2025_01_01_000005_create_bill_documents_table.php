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
        Schema::create('bill_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bill_id')->constrained('legislative_bills')->onDelete('cascade');

            // Document identification
            $table->string('document_type', 100)->index(); // bill_text, amendment, report, opinion, etc.
            $table->string('title', 255);
            $table->text('description')->nullable();

            // File information
            $table->text('url'); // Original URL
            $table->string('local_path', 255)->nullable(); // Path in storage
            $table->string('file_hash', 64)->nullable(); // SHA256 for deduplication
            $table->integer('file_size')->nullable(); // In bytes
            $table->string('mime_type', 100)->nullable();

            // Download and processing status
            $table->boolean('downloaded')->default(false)->index();
            $table->timestamp('downloaded_at')->nullable();
            $table->integer('download_attempts')->default(0);
            $table->text('download_error')->nullable();

            // Text extraction
            $table->boolean('text_extracted')->default(false)->index();
            $table->longText('extracted_text')->nullable();
            $table->timestamp('extracted_at')->nullable();

            // Version tracking
            $table->integer('version')->default(1);
            $table->date('document_date')->nullable()->index();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'document_type']);
            $table->index('file_hash');
            $table->index(['downloaded', 'text_extracted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_documents');
    }
};
