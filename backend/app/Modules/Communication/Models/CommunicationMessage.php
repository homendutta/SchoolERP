<?php

declare(strict_types=1);

namespace App\Modules\Communication\Models;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A single queued message + its delivery tracking. */
class CommunicationMessage extends Model
{
    protected $table = 'communication_messages';

    protected $fillable = [
        'school_id', 'batch_id', 'template_id', 'channel', 'recipient_type', 'recipient_id',
        'recipient_name', 'user_id', 'address', 'subject', 'body', 'status', 'is_mandatory',
        'scheduled_at', 'attempts', 'max_attempts', 'next_attempt_at',
        'sent_at', 'delivered_at', 'read_at', 'failed_at', 'error', 'provider', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'attempts' => 0, 'max_attempts' => 3];

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'status' => MessageStatus::class,
            'is_mandatory' => 'boolean',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'scheduled_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CommunicationBatch::class, 'batch_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DeliveryLog::class, 'message_id')->latest('id');
    }
}
