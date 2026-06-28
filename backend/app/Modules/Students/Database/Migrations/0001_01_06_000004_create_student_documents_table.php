<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Documents — file references only (media_id via the Media Upload
 * Pipeline). Document type comes from Master Data; never a hardcoded list and
 * never a raw path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('document_type_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('document_type_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
