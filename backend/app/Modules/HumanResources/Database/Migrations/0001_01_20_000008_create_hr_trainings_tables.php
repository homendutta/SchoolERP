<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Training programmes and their participants. Certificates are Media references.
 * Training records remain historical. Assigning a participant publishes a
 * Communication event through the engine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_trainings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('duration_hours', 6, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('status');
        });

        Schema::create('hr_training_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_id')->constrained('hr_trainings')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedBigInteger('certificate_media_id')->nullable();
            $table->string('status')->default('assigned');
            $table->date('completed_on')->nullable();
            $table->timestamps();

            $table->unique(['training_id', 'staff_id']);
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_training_participants');
        Schema::dropIfExists('hr_trainings');
    }
};
