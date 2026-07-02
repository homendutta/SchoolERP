<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vehicle documents (Insurance/Registration/Pollution/Fitness/Permit — Media
 * references only) and vehicle staff assignments (drivers + attendants, always
 * resolved from Staff Management; multiple attendants per vehicle allowed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicle_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('document_type');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vehicle_id');
            $table->index('document_type');
        });

        Schema::create('transport_vehicle_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('role'); // primary_driver | backup_driver | attendant | helper
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('staff_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vehicle_staff');
        Schema::dropIfExists('transport_vehicle_documents');
    }
};
