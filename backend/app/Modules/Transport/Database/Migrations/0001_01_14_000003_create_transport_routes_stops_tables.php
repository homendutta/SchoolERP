<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Routes (route code from the Number Generator) and their ordered stops. A route
 * is not a vehicle. Latitude/longitude are stored future-ready for GPS/live maps
 * without any structural change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('route_code');
            $table->string('name');
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->unique(['school_id', 'route_code']);
        });

        Schema::create('transport_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('route_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();  // future-ready
            $table->decimal('longitude', 10, 7)->nullable(); // future-ready
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('route_id');
            $table->index('sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
    }
};
