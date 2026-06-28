<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student ↔ Guardian links and the per-year Student Academic Record.
 *
 * The academic record is the immutable history row: promotion creates a NEW
 * record (is_current flips) and never updates a student's class in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardian', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->string('relation')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
            $table->index('student_id');
            $table->index('guardian_id');
        });

        Schema::create('student_academic_records', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('roll_number')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_current')->default(true);
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('academic_year_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('is_current');
            $table->index('created_at');
            $table->index(['student_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_records');
        Schema::dropIfExists('student_guardian');
    }
};
