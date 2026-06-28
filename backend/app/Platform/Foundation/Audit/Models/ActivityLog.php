<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Audit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row in the platform Activity Log. Append-only: created but never updated.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'school_id', 'causer_id', 'log_name', 'action', 'description',
        'subject_type', 'subject_id', 'properties', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
