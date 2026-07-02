<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hostel structure: hostels (code from the Number Generator), buildings, and
 * floors. A hostel is not a building; a building is not a floor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('gender')->default('boys');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->unique(['school_id', 'code']);
        });

        Schema::create('hostel_buildings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('hostel_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('floors_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('hostel_id');
        });

        Schema::create('hostel_floors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('building_id');
            $table->integer('floor_number')->default(0);
            $table->string('name')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('building_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_floors');
        Schema::dropIfExists('hostel_buildings');
        Schema::dropIfExists('hostels');
    }
};
