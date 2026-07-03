<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Homework (independent of the Examination module). Students submit; parents monitor. */
class Homework extends Model
{
    use SoftDeletes;

    protected $table = 'lms_homework';

    protected $fillable = [
        'school_id', 'class_id', 'section_id', 'subject_id', 'teacher_id', 'title', 'instructions',
        'attachments', 'publish_date', 'due_date', 'allow_late', 'max_marks', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'allow_late' => false];

    protected function casts(): array
    {
        return [
            'attachments' => 'array', 'publish_date' => 'date', 'due_date' => 'date',
            'allow_late' => 'boolean', 'max_marks' => 'decimal:2', 'published_at' => 'datetime', 'status' => LmsStatus::class,
        ];
    }

    public function submissions(): MorphMany
    {
        return $this->morphMany(Submission::class, 'submittable');
    }
}
