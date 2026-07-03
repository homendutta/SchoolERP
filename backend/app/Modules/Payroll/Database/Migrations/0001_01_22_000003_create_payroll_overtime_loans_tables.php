<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overtime entries and loans / advances.
 *
 * Overtime carries its own eligibility / hourly rate / maximum hours / approval;
 * Payroll only calculates APPROVED overtime. Loans/advances track principal,
 * balance and installment — Payroll deducts installments while active, but
 * Finance owns the actual cash movement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_overtime', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedInteger('period_year');
            $table->unsignedInteger('period_month');
            $table->decimal('hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('max_hours', 8, 2)->nullable();
            $table->boolean('eligible')->default(true);
            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index(['staff_id', 'period_year', 'period_month']);
        });

        Schema::create('payroll_loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('loan_type')->default('loan'); // loan / advance
            $table->string('reference')->nullable();
            $table->decimal('principal', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('installment_amount', 14, 2)->default(0);
            $table->date('disbursed_on')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status')->default('pending'); // pending / active / closed
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_loans');
        Schema::dropIfExists('payroll_overtime');
    }
};
