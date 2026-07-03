<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee salary assignments (versioned history) and salary revisions.
 *
 * Each assignment row is one immutable salary VERSION for an employee; a revision
 * creates a NEW version and closes the previous one (is_current). Previous
 * versions are never overwritten. The revisions table is the audit trail that
 * links each revision event to the version it produced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_salary_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('structure_id')->constrained('payroll_structures')->cascadeOnDelete();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('revision_number')->default(1);
            $table->string('revision_type')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index(['staff_id', 'is_current']);
        });

        Schema::create('payroll_salary_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedBigInteger('assignment_id')->nullable(); // the version produced
            $table->unsignedBigInteger('previous_assignment_id')->nullable();
            $table->unsignedBigInteger('structure_id')->nullable();
            $table->string('revision_type'); // promotion / annual_increment / special_increment / correction
            $table->date('effective_date')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_salary_revisions');
        Schema::dropIfExists('payroll_salary_assignments');
    }
};
