<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employment history and employee documents.
 *
 * An Employee (Staff) is NOT an Employment. Employment changes over time; every
 * change (department / designation / type / separation) creates a NEW record and
 * the previous one is closed (is_current = false) — history is never overwritten.
 * Documents store ONLY Media references (Media Platform owns the files).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employment_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->unsignedBigInteger('reporting_manager_id')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_current')->default(true);
            $table->string('change_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index(['staff_id', 'is_current']);
            $table->index('department_id');
            $table->index('designation_id');
            $table->index('status');
        });

        Schema::create('hr_employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('document_type'); // appointment_letter, contract, certificate, identity, qualification, experience
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('title')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('staff_id');
            $table->index('document_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_documents');
        Schema::dropIfExists('hr_employment_records');
    }
};
