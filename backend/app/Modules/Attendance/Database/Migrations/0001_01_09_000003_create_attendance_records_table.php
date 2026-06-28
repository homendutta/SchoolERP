<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The unified Attendance Engine table — serves BOTH students and staff. Every
 * record references the Platform Identity; owner_type/owner_id are denormalized
 * for fast dashboards/queries. Session comes from Master Data. The source
 * (manual/import/biometric/api) is always recorded.
 *
 * Duplicate protection: one record per identity + date + session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('identity_id')->constrained('identities')->cascadeOnDelete();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            // Student context
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            // Staff context
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();

            $table->foreignId('session_id')->nullable()->constrained('master_data_values')->nullOnDelete();
            $table->string('shift')->nullable();
            $table->date('attendance_date');
            $table->string('status');
            $table->string('source');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->boolean('is_late')->default(false);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('biometric_log_id')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('identity_id');
            $table->index(['owner_type', 'owner_id']);
            $table->index('attendance_date');
            $table->index('status');
            $table->index('source');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('department_id');
            $table->index(['owner_type', 'attendance_date']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
