<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reports & Printing Center persistence: saved reports (reusable filters/columns/
 * sorting), scheduled reports (queued, optionally emailed via Communication) and
 * the export history/queue. The report DEFINITIONS themselves are code-registered
 * (ReportRegistry) — this module never owns business data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_saved', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('report_key');
            $table->string('name');
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->json('sort')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'report_key']);
            $table->index('user_id');
        });

        Schema::create('report_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('report_key');
            $table->string('name');
            $table->string('frequency'); // daily / weekly / monthly
            $table->string('format')->default('csv');
            $table->json('filters')->nullable();
            $table->json('recipients')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'report_key']);
            $table->index('next_run_at');
        });

        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('report_key');
            $table->string('report_name')->nullable();
            $table->string('format');
            $table->string('status')->default('queued');
            $table->json('params')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedBigInteger('media_id')->nullable();
            $table->text('error')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'report_key']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('report_saved');
    }
};
