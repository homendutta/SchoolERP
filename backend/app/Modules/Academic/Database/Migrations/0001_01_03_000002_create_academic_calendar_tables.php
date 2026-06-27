<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable Academic Calendar — a platform-style service later consumed by
 * Attendance, Timetable, Examination, Homework, Leave, and Transport.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 16)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index('status');
        });

        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id']);
            $table->index('status');
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->foreignId('holiday_type_id')->nullable()->constrained('holiday_types')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type'); // working_day | holiday | half_day | examination_day | school_event | special_working_day
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_calendar_id', 'start_date']);
            $table->index('event_type');
            $table->index('start_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('academic_calendars');
        Schema::dropIfExists('holiday_types');
    }
};
