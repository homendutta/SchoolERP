<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The reusable Maintenance Engine store. A maintenance request targets ANY
 * maintainable model via a polymorphic reference (maintainable_type/id), so the
 * same engine serves Inventory assets today and Transport / Hostel / Laboratory
 * / Facilities / IT assets in future — with no structural change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('maintainable_type');
            $table->unsignedBigInteger('maintainable_id');
            $table->string('type')->default('preventive');
            $table->string('priority')->default('medium');
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index(['maintainable_type', 'maintainable_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
