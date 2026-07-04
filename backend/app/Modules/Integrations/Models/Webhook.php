<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use App\Modules\Integrations\Enums\WebhookDirection;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An incoming/outgoing webhook. Its signing secret is stored ENCRYPTED. */
class Webhook extends Model
{
    use SoftDeletes;

    protected $table = 'integration_webhooks';

    protected $fillable = [
        'school_id', 'name', 'direction', 'url', 'secret', 'events', 'max_retries', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'max_retries' => 3];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'events' => 'array',
            'max_retries' => 'integer',
            'direction' => WebhookDirection::class,
            'status' => RecordStatus::class,
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'webhook_id');
    }
}
