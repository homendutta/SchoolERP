<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biometric devices (e.g. eSSL MB20). Multiple devices per school. The Attendance
 * module is vendor-independent — device_type identifies which connector parses
 * the device's events; no vendor logic lives here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('device_type')->default('essl_mb20'); // connector key
            $table->string('device_identifier');                 // serial / unique id reported by the device
            $table->string('location')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('status');
            $table->index('created_at');
            $table->unique(['school_id', 'device_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
