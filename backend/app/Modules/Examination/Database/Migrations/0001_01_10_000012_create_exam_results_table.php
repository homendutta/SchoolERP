<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Processed aggregate result per student per session. Totals/percentage/grade/
 * GPA/pass-fail/rank are computed using ONLY the student's assigned subjects.
 * Publication writes the Audit Log and a Timeline entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_session_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->decimal('total_obtained', 8, 2)->default(0);
            $table->decimal('total_max', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->decimal('gpa', 5, 2)->nullable();
            $table->string('result_status')->default('pending');
            $table->unsignedInteger('rank')->nullable();
            $table->unsignedInteger('subjects_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id']);
            $table->index('school_id');
            $table->index(['class_id', 'section_id']);
            $table->index('result_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
