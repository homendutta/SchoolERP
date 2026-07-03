<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable work shifts and attendance policies. Office hours are NEVER
 * hardcoded — they are defined here. HR only DEFINES attendance policy; the
 * existing Attendance module CONSUMES it (no attendance logic is duplicated).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_shifts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('grace_minutes')->default(0);
            $table->json('weekly_off_pattern')->nullable(); // e.g. [0,6] (Sun, Sat)
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });

        Schema::create('hr_attendance_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('grace_minutes')->default(0);
            $table->decimal('half_day_hours', 5, 2)->nullable();
            $table->unsignedInteger('late_after_minutes')->nullable();
            $table->boolean('overtime_eligible')->default(false);
            $table->decimal('minimum_working_hours', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_attendance_policies');
        Schema::dropIfExists('hr_shifts');
    }
};
