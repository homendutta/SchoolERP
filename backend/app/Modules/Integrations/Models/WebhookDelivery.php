<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use App\Modules\Integrations\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A webhook delivery attempt log (retried on the queue). */
class WebhookDelivery extends Model
{
    protected $table = 'integration_webhook_deliveries';

    protected $fillable = [
        'school_id', 'webhook_id', 'event', 'payload', 'signature', 'status',
        'attempts', 'response_code', 'delivered_at', 'next_retry_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'attempts' => 0];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'response_code' => 'integer',
            'delivered_at' => 'datetime',
            'next_retry_at' => 'datetime',
            'status' => DeliveryStatus::class,
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
