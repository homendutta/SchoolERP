<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll runs, payslips and payslip lines.
 *
 * A run (run_number from the Number Generator) is immutable once Locked. A
 * payslip (payslip_number from the Number Generator) is a generated financial
 * document with its earnings/deductions/employer lines. The unique
 * (staff_id, period_year, period_month) guarantees payroll processing is
 * IDEMPOTENT — running twice never creates a duplicate payroll record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('run_number')->nullable();
            $table->string('label')->nullable();
            $table->unsignedInteger('period_year');
            $table->unsignedInteger('period_month');
            $table->string('status')->default('draft'); // draft / processing / completed / locked / cancelled
            $table->decimal('total_earnings', 16, 2)->default(0);
            $table->decimal('total_deductions', 16, 2)->default(0);
            $table->decimal('total_employer', 16, 2)->default(0);
            $table->decimal('total_net', 16, 2)->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['school_id', 'period_year', 'period_month']);
            $table->index('status');
        });

        Schema::create('payroll_payslips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->string('payslip_number')->nullable();
            $table->unsignedInteger('period_year');
            $table->unsignedInteger('period_month');
            $table->decimal('gross_earnings', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('employer_contributions', 14, 2)->default(0);
            $table->decimal('net_pay', 14, 2)->default(0);
            $table->decimal('present_days', 6, 2)->default(0);
            $table->decimal('absent_days', 6, 2)->default(0);
            $table->decimal('leave_days', 6, 2)->default(0);
            $table->decimal('lwp_days', 6, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 14, 2)->default(0);
            $table->string('settlement_status')->default('unpaid');
            $table->timestamps();

            $table->unique(['staff_id', 'period_year', 'period_month']);
            $table->index('school_id');
            $table->index('run_id');
        });

        Schema::create('payroll_payslip_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payroll_payslips')->cascadeOnDelete();
            $table->unsignedBigInteger('component_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('line_type'); // earning / deduction / employer_contribution / informational
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index('payslip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payslip_lines');
        Schema::dropIfExists('payroll_payslips');
        Schema::dropIfExists('payroll_runs');
    }
};
