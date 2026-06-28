<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One event in a student's timeline. Append-only; shown newest first.
 */
class StudentTimeline extends Model
{
    protected $table = 'student_timelines';

    protected $fillable = [
        'student_id', 'event_type', 'title', 'description', 'performed_by', 'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
