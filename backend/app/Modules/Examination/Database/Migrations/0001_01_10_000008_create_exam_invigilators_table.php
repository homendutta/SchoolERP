<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invigilator assignment — one or more Staff members per scheduled exam (reused
 * from Staff). Substitution is supported later via status changes / new rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_invigilators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('exam_schedule_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('role')->default('assistant');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_schedule_id', 'staff_id']);
            $table->index('school_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_invigilators');
    }
};
