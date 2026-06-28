<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable biometric event log — every raw event a device pushes is recorded
 * here BEFORE processing. People are identified by their Identity Number (never
 * a student/staff id). These rows are audit records and are never deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('biometric_devices')->nullOnDelete();
            $table->string('identity_number')->nullable();
            $table->timestamp('event_time');
            $table->string('direction')->nullable();             // in | out
            $table->json('raw_payload')->nullable();
            $table->string('processing_status')->default('pending');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('device_id');
            $table->index('identity_number');
            $table->index('processing_status');
            $table->index('event_time');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_logs');
    }
};
