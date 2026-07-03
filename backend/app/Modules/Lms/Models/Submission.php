<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\SubmissionStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** An immutable student submission version (homework or assignment). */
class Submission extends Model
{
    use InteractsWithMedia;

    protected $table = 'lms_submissions';

    protected $fillable = [
        'school_id', 'submittable_type', 'submittable_id', 'student_id', 'version',
        'content', 'attachments', 'links', 'submitted_at', 'is_late', 'status', 'marks',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'submitted', 'version' => 1, 'is_late' => false];

    protected function casts(): array
    {
        return [
            'attachments' => 'array', 'links' => 'array', 'submitted_at' => 'datetime',
            'is_late' => 'boolean', 'version' => 'integer', 'marks' => 'decimal:2', 'status' => SubmissionStatus::class,
        ];
    }

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'submission_id');
    }
}
