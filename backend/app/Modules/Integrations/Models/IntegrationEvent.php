<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use Illuminate\Database\Eloquent\Model;

/** An IMMUTABLE integration event (the event bus). Never updated after dispatch. */
class IntegrationEvent extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'integration_events';

    protected $fillable = ['school_id', 'event', 'source', 'payload', 'dispatched_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'dispatched_at' => 'datetime'];
    }
}
