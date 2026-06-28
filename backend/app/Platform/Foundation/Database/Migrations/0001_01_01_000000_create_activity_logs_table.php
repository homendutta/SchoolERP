<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform Activity Log — a reusable, append-only audit trail. Any module can
 * record a domain action (who did what to which subject) without coupling to a
 * concrete model. Columns are indexed but carry no FK constraints so the log is
 * never blocked by, and never blocks, the records it describes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('log_name')->default('default');
            $table->string('action');
            $table->string('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('school_id');
            $table->index('causer_id');
            $table->index('log_name');
            $table->index('action');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
