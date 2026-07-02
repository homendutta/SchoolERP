<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asset warranties (reminder events published via Communication). Maintenance is
 * NOT owned here — it is handled by the reusable Platform Maintenance Engine
 * (maintenance_requests, polymorphic maintainable), which Inventory consumes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_warranties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('coverage')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('school_id');
            $table->index('asset_id');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_warranties');
    }
};
