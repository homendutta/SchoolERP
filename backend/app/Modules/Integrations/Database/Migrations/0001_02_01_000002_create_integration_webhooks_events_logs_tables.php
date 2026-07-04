<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Webhooks (incoming + outgoing, with signature secret + retry), their delivery
 * log (retried on the queue), the immutable integration event bus and the request
 * log. Every integration request/failure is logged; events are never mutated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_webhooks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('direction'); // incoming / outgoing
            $table->string('url')->nullable();
            $table->longText('secret')->nullable(); // ENCRYPTED (HMAC signing key)
            $table->json('events')->nullable();
            $table->unsignedInteger('max_retries')->default(3);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'direction']);
        });

        Schema::create('integration_webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('webhook_id')->nullable();
            $table->string('event')->nullable();
            $table->json('payload')->nullable();
            $table->string('signature')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index('webhook_id');
            $table->index('status');
        });

        Schema::create('integration_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('event');
            $table->string('source')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['school_id', 'event']);
        });

        Schema::create('integration_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->string('provider_code')->nullable();
            $table->string('method')->nullable();
            $table->string('url')->nullable();
            $table->string('status'); // success / failure
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['school_id', 'provider_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('integration_events');
        Schema::dropIfExists('integration_webhook_deliveries');
        Schema::dropIfExists('integration_webhooks');
    }
};
