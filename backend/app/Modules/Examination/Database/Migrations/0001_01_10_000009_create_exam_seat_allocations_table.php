<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seating plan: a student is allocated a seat in a room for a scheduled exam.
 * Room capacity (reused from Academic) is validated on allocation and never
 * exceeded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_schedule_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('student_id');
            $table->string('seat_number')->nullable();
            $table->string('roll_number')->nullable();
            $table->timestamps();

            $table->unique(['exam_schedule_id', 'student_id']);
            $table->index('school_id');
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_allocations');
    }
};
