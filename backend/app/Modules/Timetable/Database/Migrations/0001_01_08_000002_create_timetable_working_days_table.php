<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable working days. Schools choose which weekdays are working days —
 * never hardcode Monday–Friday. One row per school + weekday.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_working_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('weekday');
            $table->boolean('is_working')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'weekday']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_working_days');
    }
};
