<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arrears (applied during processing, historical) and statutory components
 * (PF / ESI / Professional Tax / TDS / Other). Statutory RATES are never
 * hardcoded — only the configuration is stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_arrears', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('arrear_type')->default('salary'); // salary / adjustment
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reason')->nullable();
            $table->boolean('applied')->default(false);
            $table->unsignedBigInteger('applied_run_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('applied');
        });

        Schema::create('payroll_statutory_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('statutory_type'); // pf / esi / professional_tax / tds / other
            $table->string('calculation_type')->default('percentage'); // fixed / percentage
            $table->decimal('employee_rate', 8, 4)->default(0);
            $table->decimal('employer_rate', 8, 4)->default(0);
            $table->string('based_on')->nullable(); // e.g. 'basic' / 'gross'
            $table->decimal('wage_ceiling', 14, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('statutory_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_statutory_components');
        Schema::dropIfExists('payroll_arrears');
    }
};
