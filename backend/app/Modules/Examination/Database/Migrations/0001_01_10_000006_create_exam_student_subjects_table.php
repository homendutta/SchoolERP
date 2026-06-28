<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The AUTHORITATIVE list of subjects assigned to each student for a session.
 *
 * This is the single source of truth that makes optional/elective subjects
 * correct everywhere: a student only ever receives marks for, is graded on, is
 * shown on a report card for, and is promoted against the subjects recorded
 * here. A subject the student did not take simply has no row — so it can never
 * appear as "failed".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_student_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_session_id');
            $table->unsignedBigInteger('exam_subject_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['exam_subject_id', 'student_id']);
            $table->index('exam_session_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_student_subjects');
    }
};
