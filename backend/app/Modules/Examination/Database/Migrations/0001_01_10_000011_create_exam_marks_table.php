<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks per student per exam subject. Supports multiple components (component_id
 * set) or a single total (component_id null). Marks are only ever stored for
 * subjects the student is assigned (exam_student_subjects). Duplicate protection
 * is enforced in the MarksService (null-component safe).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_marks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_subject_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('component_id')->nullable();
            $table->decimal('marks_obtained', 6, 2)->nullable();
            $table->decimal('max_marks', 6, 2);
            $table->boolean('is_absent')->default(false);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('exam_subject_id');
            $table->index('student_id');
            $table->index(['exam_subject_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_marks');
    }
};
