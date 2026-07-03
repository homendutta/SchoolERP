<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Disciplinary records (warning / suspension / notice / termination
 * recommendation / other). Supporting documents are Media references. Complete
 * history is maintained — records are never deleted or overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_disciplinary_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('action_type');
            $table->date('incident_date')->nullable();
            $table->date('action_date')->nullable();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_disciplinary_records');
    }
};
