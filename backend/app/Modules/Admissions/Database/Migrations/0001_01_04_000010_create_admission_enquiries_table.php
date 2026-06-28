<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admission Enquiries — prospective-student interest captured before any
 * application exists. Source is Master Data (never hardcoded).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('enquiry_number');
            $table->string('student_name');
            $table->string('guardian_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('class_interested')->nullable();
            $table->foreignId('source_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->string('status')->default('new');
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->unsignedBigInteger('converted_application_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('academic_year_id');
            $table->index('status');
            $table->index('follow_up_date');
            $table->index('created_at');
            $table->unique(['school_id', 'enquiry_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_enquiries');
    }
};
