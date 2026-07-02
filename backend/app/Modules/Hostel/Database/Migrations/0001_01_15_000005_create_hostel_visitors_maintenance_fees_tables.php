<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hostel visitors (ID proof via Media), maintenance requests (no workflow
 * engine), and hostel fee definitions (Hostel never collects money — Finance
 * manages billing/payment).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_visitors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('visitor_name');
            $table->string('identity_proof')->nullable();
            $table->unsignedBigInteger('id_media_id')->nullable(); // Media reference
            $table->date('visit_date')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('purpose')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('status');
        });

        Schema::create('hostel_maintenance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('reported_by')->nullable();
            $table->string('category')->default('other');
            $table->string('priority')->default('medium');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->date('resolution_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('hostel_id');
            $table->index('status');
            $table->index('priority');
        });

        Schema::create('hostel_fees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('fee_type')->default('hostel');
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('finance_fee_master_id')->nullable(); // Finance link
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('hostel_id');
            $table->index('fee_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_fees');
        Schema::dropIfExists('hostel_maintenance');
        Schema::dropIfExists('hostel_visitors');
    }
};
