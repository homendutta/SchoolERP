<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Timeline — a reusable, append-only event log per student. Every module
 * (Fees, Attendance, …) writes important events here; the UI shows newest first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_timelines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_timelines');
    }
};
