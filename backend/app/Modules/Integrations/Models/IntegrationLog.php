<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Models;

use App\Modules\Integrations\Enums\LogStatus;
use Illuminate\Database\Eloquent\Model;

/** A logged integration request (every request + failure is recorded). */
class IntegrationLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'integration_logs';

    protected $fillable = [
        'school_id', 'provider_id', 'provider_code', 'method', 'url',
        'status', 'response_code', 'duration_ms', 'error',
    ];

    protected function casts(): array
    {
        return [
            'response_code' => 'integer',
            'duration_ms' => 'integer',
            'status' => LogStatus::class,
        ];
    }
}
