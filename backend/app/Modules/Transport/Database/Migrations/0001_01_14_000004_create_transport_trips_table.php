<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scheduled trips — daily operations. A trip runs a route using an assigned
 * vehicle + driver (+ optional attendant), for an academic year and shift. The
 * system determines a student's vehicle through the trip (students are never
 * assigned directly to a vehicle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('driver_id')->nullable();       // Staff
            $table->unsignedBigInteger('attendant_id')->nullable();    // Staff
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('shift')->default('morning');
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('vehicle_id');
            $table->index('route_id');
            $table->index('driver_id');
            $table->index('shift');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trips');
    }
};
