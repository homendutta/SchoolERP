<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transport fee DEFINITIONS (route / stop / special) — Transport never collects
 * money; Finance manages payment. And vehicle maintenance SCHEDULES only
 * (service due date, odometer, reminder) — no maintenance workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_fees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('fee_type')->default('route'); // route | stop | special
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('stop_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('finance_fee_master_id')->nullable(); // Finance link (future)
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('fee_type');
            $table->index('route_id');
        });

        Schema::create('transport_maintenance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('service_type')->nullable();
            $table->date('service_due_date')->nullable();
            $table->unsignedBigInteger('odometer_due')->nullable();
            $table->date('last_service_date')->nullable();
            $table->unsignedInteger('reminder_days')->default(7);
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('vehicle_id');
            $table->index('service_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_maintenance');
        Schema::dropIfExists('transport_fees');
    }
};
