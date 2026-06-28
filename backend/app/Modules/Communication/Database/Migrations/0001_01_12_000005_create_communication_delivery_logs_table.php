<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complete delivery history per message (created → queued → sent → delivered →
 * read, or failed/retried). Immutable audit of every state transition.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_delivery_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('event'); // created | queued | sent | delivered | read | failed | retried | cancelled
            $table->string('detail')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('message_id');
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_delivery_logs');
    }
};
