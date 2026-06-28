<?php

declare(strict_types=1);

namespace App\Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One event in an employee's timeline. Append-only; shown newest first.
 */
class StaffTimeline extends Model
{
    protected $table = 'staff_timelines';

    protected $fillable = [
        'staff_id', 'event_type', 'title', 'description', 'performed_by', 'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
