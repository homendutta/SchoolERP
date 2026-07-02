<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vehicles. Vehicle number comes from the Number Generator; registration number
 * is the government registration. Photo is a Media reference; documents live in
 * their own table (Media references only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('vehicle_number');
            $table->string('registration_number')->nullable();
            $table->string('vehicle_type')->default('bus');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('seating_capacity')->default(0);
            $table->unsignedInteger('reserved_seats')->default(0);
            $table->string('fuel_type')->default('diesel');
            $table->unsignedBigInteger('odometer')->nullable();
            $table->unsignedBigInteger('photo_media_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('status');
            $table->unique(['school_id', 'vehicle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vehicles');
    }
};
