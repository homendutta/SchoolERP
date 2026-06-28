<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Audit;

use App\Platform\Foundation\Audit\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Reusable Activity Logger. Records a domain action against an optional subject
 * model. Any module resolves this from the container and calls log()/record().
 */
class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = [],
        ?int $schoolId = null,
        string $logName = 'default',
    ): ActivityLog {
        return ActivityLog::create([
            'school_id' => $schoolId,
            'causer_id' => Auth::id(),
            'log_name' => $logName,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject !== null ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
