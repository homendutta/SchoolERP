<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Temporary teacher substitutions for a specific date + period. These are
 * SEPARATE records — they never modify the master class timetable. A
 * substitution may reference the covered slot for context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_substitutions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('class_timetable_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('original_teacher_id')->nullable();
            $table->unsignedBigInteger('substitute_teacher_id');
            $table->date('date');
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('date');
            $table->index('period_id');
            $table->index('original_teacher_id');
            $table->index('substitute_teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_substitutions');
    }
};
