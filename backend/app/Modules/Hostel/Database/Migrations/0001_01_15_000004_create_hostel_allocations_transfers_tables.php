<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student bed allocations (never to a room directly) and transfer records. The
 * full path (hostel → building → floor → room → bed) is denormalized. History is
 * NEVER overwritten — a transfer/checkout closes the current record and a new
 * one is created.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('hostel_id');
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('floor_id')->nullable();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('bed_id');
            $table->date('allocation_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('hostel_id');
            $table->index('room_id');
            $table->index('bed_id');
            $table->index('status');
        });

        Schema::create('hostel_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('from_allocation_id')->nullable();
            $table->unsignedBigInteger('to_allocation_id')->nullable();
            $table->unsignedBigInteger('from_bed_id')->nullable();
            $table->unsignedBigInteger('to_bed_id')->nullable();
            $table->string('transfer_type')->nullable(); // room | bed | building | hostel
            $table->text('reason')->nullable();
            $table->date('transfer_date')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_transfers');
        Schema::dropIfExists('hostel_allocations');
    }
};
