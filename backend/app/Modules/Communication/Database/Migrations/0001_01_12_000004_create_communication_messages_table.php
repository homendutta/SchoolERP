<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The message queue + delivery tracking. One row per recipient per channel.
 * Messages are never lost: a failed message is retried (back to pending) using
 * the configurable retry policy until it succeeds or attempts are exhausted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('channel');
            $table->string('recipient_type')->nullable();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('address')->nullable(); // email / phone / device token
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status')->default('pending');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->string('provider')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('batch_id');
            $table->index('status');
            $table->index('channel');
            $table->index('scheduled_at');
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_messages');
    }
};
