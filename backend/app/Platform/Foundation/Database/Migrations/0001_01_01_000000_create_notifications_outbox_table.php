<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform Notification outbox — every email/SMS the system attempts is recorded
 * here (channel, recipient, status). The shared NotificationService writes the
 * record, then dispatches via the configured gateway. Modules never talk to a
 * mailer/SMS provider directly, so notification logic is implemented once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_outbox', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('channel');           // email | sms
            $table->string('recipient');         // email address or phone
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status')->default('queued'); // queued | sent | failed | skipped
            $table->string('error')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('channel');
            $table->index('status');
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_outbox');
    }
};
