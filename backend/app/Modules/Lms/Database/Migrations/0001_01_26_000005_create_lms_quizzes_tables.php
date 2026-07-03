<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LMS quizzes, their questions and student attempts. These are LEARNING quizzes —
 * they are NOT Examination-module exams and never write exam records. Attempts
 * track timing, score and attempt number, honouring a configurable attempt limit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quizzes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('time_limit')->nullable();   // minutes
            $table->decimal('passing_marks', 8, 2)->nullable();
            $table->boolean('random_order')->default(false);
            $table->boolean('immediate_result')->default(true);
            $table->unsignedInteger('max_attempts')->default(1);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'subject_id']);
        });

        Schema::create('lms_quiz_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->string('type'); // multiple_choice / true_false / short_answer / fill_blank
            $table->text('question');
            $table->json('options')->nullable();  // for choice types
            $table->json('answer')->nullable();   // correct answer(s)
            $table->decimal('marks', 8, 2)->default(1);
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();

            $table->index('quiz_id');
        });

        Schema::create('lms_quiz_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->unsignedInteger('time_taken')->nullable(); // seconds
            $table->boolean('passed')->nullable();
            $table->json('responses')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_attempts');
        Schema::dropIfExists('lms_quiz_questions');
        Schema::dropIfExists('lms_quizzes');
    }
};
