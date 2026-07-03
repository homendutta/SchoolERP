<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance reviews. Every review (period, reviewer, goals, rating, comments,
 * development plan) is stored as history and never overwritten. A scheduled
 * review publishes a Communication event through the engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_performance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->date('review_period_start')->nullable();
            $table->date('review_period_end')->nullable();
            $table->text('goals')->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->text('comments')->nullable();
            $table->text('development_plan')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_performance_reviews');
    }
};
