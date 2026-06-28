<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single notification attempt (email or SMS) recorded by the platform
 * NotificationService.
 */
class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';

    protected $fillable = [
        'school_id', 'channel', 'recipient', 'subject', 'body', 'status',
        'error', 'notifiable_type', 'notifiable_id', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
