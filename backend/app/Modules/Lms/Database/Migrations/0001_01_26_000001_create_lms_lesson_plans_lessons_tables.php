<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lesson plans, lessons and per-student lesson completions.
 *
 * Subjects/classes/sections/teachers are REFERENCED from the Academic module
 * (never duplicated). `teacher_id` is the teaching User (matches
 * teacher_subject_assignments). Lessons carry rich text + Media attachments +
 * external links + embedded videos, and publish immediately or on a schedule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_lesson_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id'); // teaching User
            $table->string('title');
            $table->text('objectives')->nullable();
            $table->text('topics')->nullable();
            $table->string('teaching_method')->nullable();
            $table->date('planned_date')->nullable();
            $table->string('completion_status')->default('planned'); // planned / in_progress / completed
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'subject_id']);
            $table->index('teacher_id');
        });

        Schema::create('lms_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lesson_plan_id')->constrained('lms_lesson_plans')->cascadeOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->json('attachments')->nullable();      // Media ids
            $table->json('external_links')->nullable();
            $table->json('embedded_videos')->nullable();
            $table->unsignedInteger('estimated_duration')->nullable(); // minutes
            $table->unsignedInteger('reading_time')->nullable();       // minutes
            $table->string('status')->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('lesson_plan_id');
        });

        Schema::create('lms_lesson_completions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lms_lessons')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['lesson_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_lesson_completions');
        Schema::dropIfExists('lms_lessons');
        Schema::dropIfExists('lms_lesson_plans');
    }
};
