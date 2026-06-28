<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The master class timetable. Each row is ONE slot: academic year + class +
 * section + weekday + period → subject + teacher + room. Teacher and Room
 * timetables are DERIVED from this table (never stored separately).
 *
 * Clash protection (enforced in the engine, scoped by template):
 *   - a teacher cannot occupy two slots in the same weekday+period,
 *   - a room cannot host two slots in the same weekday+period,
 *   - a class+section cannot have two subjects in the same weekday+period.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_timetables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('weekday');
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('template_id');
            $table->index('academic_year_id');
            $table->index(['class_id', 'section_id']);
            $table->index('weekday');
            $table->index('period_id');
            $table->index('subject_id');
            $table->index('teacher_id');
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_timetables');
    }
};
