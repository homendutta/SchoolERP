<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The asset lifecycle Timeline — one immutable record per lifecycle transition
 * (Draft → Ordered → Received → Available → Assigned → Reserved → In Maintenance
 * → Lost / Stolen / Disposed). Never overwritten; disposed assets keep their
 * full history and remain searchable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_lifecycle_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('asset_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('school_id');
            $table->index('asset_id');
            $table->index('to_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_lifecycle_events');
    }
};
