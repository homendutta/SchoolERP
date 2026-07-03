<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable holidays (national / state / school / optional). The Academic
 * Calendar MAY reference these; calendar logic is NOT duplicated here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->date('end_date')->nullable();
            $table->string('holiday_type')->default('school');
            $table->boolean('is_optional')->default(false);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('date');
            $table->index('holiday_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_holidays');
    }
};
