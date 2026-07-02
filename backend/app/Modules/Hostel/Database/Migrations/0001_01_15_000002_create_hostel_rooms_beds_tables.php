<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rooms (room type from Master Data; capacity enforced) and beds (bed code from
 * the Number Generator). A room is not a bed — students occupy beds, never rooms
 * directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id');
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('floor_id');
            $table->string('room_number');
            $table->unsignedBigInteger('room_type_id')->nullable(); // Master Data
            $table->unsignedInteger('capacity')->default(1);
            $table->unsignedBigInteger('photo_media_id')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('hostel_id');
            $table->index('building_id');
            $table->index('floor_id');
        });

        Schema::create('hostel_beds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('room_id');
            $table->string('bed_number');
            $table->string('bed_code')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('room_id');
            $table->index('status');
            $table->unique(['school_id', 'bed_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
    }
};
