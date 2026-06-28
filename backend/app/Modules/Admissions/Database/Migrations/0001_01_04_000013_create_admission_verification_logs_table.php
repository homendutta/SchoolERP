<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admission Verification Logs — append-only history of every verification state
 * change on an application or one of its documents. History is never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_verification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('school_id');
            $table->index('application_id');
            $table->index('document_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_verification_logs');
    }
};
