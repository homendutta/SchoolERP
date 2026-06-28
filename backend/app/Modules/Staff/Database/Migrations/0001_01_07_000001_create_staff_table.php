<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff — the employee master record for ALL employees (not only teachers).
 * Department and Designation are Master Data references (never hardcoded).
 * Photo is a Media reference; employee_number comes from the Number Generator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_number');

            // Personal (gender & blood group are Master Data, never free text)
            $table->string('name');
            $table->foreignId('gender_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->foreignId('blood_group_id')->nullable()->constrained('master_data_values')->nullOnDelete();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            // Employment (department/designation = Master Data)
            $table->foreignId('department_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->string('employment_type')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('confirmation_date')->nullable();
            // Self-referencing reporting line (nullable; detaches on manager delete).
            $table->foreignId('reporting_manager_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('is_teaching')->default(false);

            $table->string('status')->default('active');
            $table->unsignedBigInteger('photo_media_id')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('user_id');
            $table->index('department_id');
            $table->index('designation_id');
            $table->index('status');
            $table->index('is_teaching');
            $table->index('reporting_manager_id');
            $table->index('created_at');
            $table->unique(['school_id', 'employee_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
