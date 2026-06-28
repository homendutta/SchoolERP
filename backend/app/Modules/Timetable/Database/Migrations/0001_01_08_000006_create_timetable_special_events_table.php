<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timetable overrides for special events (Sports Day, Annual Function, Holiday,
 * Exam Week, Festival …). Stored SEPARATELY — they never overwrite the master
 * timetable. Scope can be the whole school, a class, or a section.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_special_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('name');
            $table->string('event_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('scope')->default('school'); // school | class | section
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->boolean('cancels_classes')->default(true);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('start_date');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_special_events');
    }
};
