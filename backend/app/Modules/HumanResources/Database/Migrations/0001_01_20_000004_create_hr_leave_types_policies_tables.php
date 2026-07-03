<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leave types and leave policies — deliberately SEPARATE concepts. A leave type
 * is the kind of leave (Casual, Sick, Earned, …; nothing hardcoded). A leave
 * policy attaches allocation / carry-forward / encashment / negative-balance /
 * approval-levels to a type. Future Payroll consumes the resulting balances.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_leave_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });

        Schema::create('hr_leave_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('annual_allocation', 6, 2)->default(0);
            $table->boolean('carry_forward')->default(false);
            $table->decimal('carry_forward_limit', 6, 2)->nullable();
            $table->boolean('encashment_allowed')->default(false);
            $table->boolean('negative_balance_allowed')->default(false);
            $table->unsignedInteger('approval_levels')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('leave_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_leave_policies');
        Schema::dropIfExists('hr_leave_types');
    }
};
