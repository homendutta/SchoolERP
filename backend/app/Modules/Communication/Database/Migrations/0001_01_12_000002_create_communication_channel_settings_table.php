<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school channel configuration: enable/disable, the bound provider, and the
 * configurable retry policy (count / delay / backoff). Nothing hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channel_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('channel');
            $table->boolean('is_enabled')->default(true);
            $table->string('provider')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedInteger('retry_delay_seconds')->default(60);
            $table->string('backoff')->default('exponential');
            $table->timestamps();

            $table->unique(['school_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_channel_settings');
    }
};
