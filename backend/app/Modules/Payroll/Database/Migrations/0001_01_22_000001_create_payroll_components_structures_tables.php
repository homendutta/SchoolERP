<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable salary components and reusable, versioned salary structures.
 *
 * A component is an earning / deduction / employer contribution / informational
 * line, valued as fixed / percentage / formula (formula stored for the future,
 * never hardcoded). A structure groups components (with optional per-structure
 * value overrides) and is versioned so history is preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('component_type'); // earning / deduction / employer_contribution / informational
            $table->string('calculation_type')->default('fixed'); // fixed / percentage / formula
            $table->decimal('default_value', 14, 2)->default(0);
            $table->string('based_on')->nullable(); // e.g. 'basic' for a percentage component
            $table->text('formula')->nullable(); // future-ready; stored, not evaluated
            $table->boolean('taxable')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('component_type');
        });

        Schema::create('payroll_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('grade')->nullable();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });

        Schema::create('payroll_structure_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('structure_id')->constrained('payroll_structures')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('payroll_components')->cascadeOnDelete();
            $table->decimal('value', 14, 2)->nullable(); // overrides the component default
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();

            $table->unique(['structure_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_structure_components');
        Schema::dropIfExists('payroll_structures');
        Schema::dropIfExists('payroll_components');
    }
};
