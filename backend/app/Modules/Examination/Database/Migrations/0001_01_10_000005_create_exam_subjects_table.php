<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subject mapping for a session: which subject is examined for a class/section,
 * with its max/passing marks. Subjects are REUSED from Academic (never
 * duplicated). subject_type_id is Master Data (Core / Elective I / II …) so
 * optional subjects are configurable, never hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('subject_type_id')->nullable(); // Master Data: Core/Elective
            $table->boolean('is_elective')->default(false);
            $table->decimal('max_marks', 6, 2)->default(100);
            $table->decimal('passing_marks', 6, 2)->default(33);
            $table->boolean('has_components')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('exam_session_id');
            $table->index(['class_id', 'section_id']);
            $table->index('subject_id');
            $table->index('is_elective');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};
