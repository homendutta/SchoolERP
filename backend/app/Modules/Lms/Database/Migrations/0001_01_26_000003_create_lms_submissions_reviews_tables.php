<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student submissions (for homework OR assignments — polymorphic) and teacher
 * reviews. Submission history is IMMUTABLE: a re-submission creates a new row
 * with an incremented version; existing rows are never overwritten. Reviews are
 * append-only (comment / grade / return / approve).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('submittable_type'); // homework / assignment model
            $table->unsignedBigInteger('submittable_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedInteger('version')->default(1);
            $table->text('content')->nullable();
            $table->json('attachments')->nullable(); // Media ids
            $table->json('links')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->string('status')->default('submitted');
            $table->decimal('marks', 8, 2)->nullable(); // set on grading
            $table->timestamps();

            $table->index(['submittable_type', 'submittable_id']);
            $table->index(['student_id']);
        });

        Schema::create('lms_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('lms_submissions')->cascadeOnDelete();
            $table->unsignedBigInteger('reviewer_id'); // teaching User
            $table->string('action'); // comment / grade / return / approve
            $table->text('comment')->nullable();
            $table->decimal('marks', 8, 2)->nullable();
            $table->timestamps();

            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_reviews');
        Schema::dropIfExists('lms_submissions');
    }
};
