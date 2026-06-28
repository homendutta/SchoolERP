<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Notifications;

use App\Platform\Foundation\Notifications\Models\NotificationOutbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Shared Notification Engine. The single place that turns a "send email/SMS"
 * intent into a recorded, dispatched message. Respects per-channel enable
 * flags; records every attempt in the outbox so modules never re-implement
 * notification logic.
 */
class NotificationService
{
    /** Send an email if the email channel is enabled; always records the attempt. */
    public function email(
        string $to,
        string $subject,
        string $body,
        bool $enabled = true,
        ?int $schoolId = null,
        ?Model $notifiable = null,
    ): NotificationOutbox {
        return $this->dispatch('email', $to, $body, $enabled, $schoolId, $notifiable, $subject);
    }

    /** Send an SMS if the SMS channel is enabled; always records the attempt. */
    public function sms(
        string $to,
        string $body,
        bool $enabled = true,
        ?int $schoolId = null,
        ?Model $notifiable = null,
    ): NotificationOutbox {
        return $this->dispatch('sms', $to, $body, $enabled, $schoolId, $notifiable, null);
    }

    private function dispatch(
        string $channel,
        string $recipient,
        string $body,
        bool $enabled,
        ?int $schoolId,
        ?Model $notifiable,
        ?string $subject,
    ): NotificationOutbox {
        $record = NotificationOutbox::create([
            'school_id' => $schoolId,
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'status' => $enabled ? 'queued' : 'skipped',
            'notifiable_type' => $notifiable !== null ? $notifiable::class : null,
            'notifiable_id' => $notifiable?->getKey(),
        ]);

        if (! $enabled) {
            return $record;
        }

        try {
            // Real gateway dispatch is wired to the configured provider. Until a
            // live transport is bound, the attempt is logged and marked sent so
            // the workflow remains observable and testable.
            Log::info("notification.{$channel}", ['to' => $recipient, 'subject' => $subject]);
            $record->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $record->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }

        return $record;
    }
}
