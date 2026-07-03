<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An assignment (independent of the Examination module). Supports grading. */
class Assignment extends Model
{
    use SoftDeletes;

    protected $table = 'lms_assignments';

    protected $fillable = [
        'school_id', 'class_id', 'section_id', 'subject_id', 'teacher_id', 'title', 'description',
        'instructions', 'attachments', 'max_marks', 'publish_date', 'due_date', 'allow_late', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'allow_late' => false];

    protected function casts(): array
    {
        return [
            'attachments' => 'array', 'max_marks' => 'decimal:2', 'publish_date' => 'date', 'due_date' => 'date',
            'allow_late' => 'boolean', 'published_at' => 'datetime', 'status' => LmsStatus::class,
        ];
    }

    public function submissions(): MorphMany
    {
        return $this->morphMany(Submission::class, 'submittable');
    }
}
