<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asset assignments (to staff / department / room / hostel / library / lab) and
 * transfers. Both are HISTORICAL — a return/transfer closes the current record
 * and creates a new one; history is never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->string('target_type'); // staff | department | room | hostel | library | laboratory
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_label')->nullable();
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->date('assigned_on')->nullable();
            $table->date('returned_on')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('asset_id');
            $table->index('status');
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('asset_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('from_assignment_id')->nullable();
            $table->unsignedBigInteger('to_assignment_id')->nullable();
            $table->string('from_label')->nullable();
            $table->string('to_label')->nullable();
            $table->string('transfer_type')->nullable();
            $table->text('reason')->nullable();
            $table->date('transfer_date')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_assignments');
    }
};
