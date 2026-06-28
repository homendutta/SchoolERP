<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff Timeline — a reusable, append-only event log per employee. Every module
 * (Payroll, Leave, Attendance, …) writes important events here; shown newest first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timelines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('staff_id');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['staff_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timelines');
    }
};
