<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Learning materials, homework and assignments. Homework and Assignments are
 * INDEPENDENT of the Examination module (no exam records are touched). Files are
 * Media references.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('pdf');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('topic')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'subject_id']);
        });

        Schema::create('lms_homework', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->json('attachments')->nullable();
            $table->date('publish_date')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('allow_late')->default(false);
            $table->decimal('max_marks', 8, 2)->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'class_id', 'section_id']);
            $table->index('subject_id');
        });

        Schema::create('lms_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('attachments')->nullable();
            $table->decimal('max_marks', 8, 2)->nullable();
            $table->date('publish_date')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('allow_late')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'class_id', 'section_id']);
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
        Schema::dropIfExists('lms_homework');
        Schema::dropIfExists('lms_materials');
    }
};
