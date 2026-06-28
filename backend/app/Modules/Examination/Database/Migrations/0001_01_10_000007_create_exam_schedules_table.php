<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Examination schedule, built on the Timetable infrastructure: each exam subject
 * is scheduled at a date + period (reused from Timetable) in a room (reused from
 * Academic) for a duration. Clash detection prevents room / class collisions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_session_id');
            $table->unsignedBigInteger('exam_subject_id');
            $table->date('exam_date');
            $table->unsignedBigInteger('period_id')->nullable(); // Timetable period
            $table->time('start_time')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedInteger('duration_minutes')->default(180);
            $table->string('status')->default('scheduled');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('exam_session_id');
            $table->index('exam_subject_id');
            $table->index('exam_date');
            $table->index('period_id');
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
