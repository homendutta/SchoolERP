<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A classroom discussion topic (teacher-created, student-participated, moderated). */
class Discussion extends Model
{
    use SoftDeletes;

    protected $table = 'lms_discussions';

    protected $fillable = [
        'school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id',
        'title', 'body', 'locked', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'published', 'locked' => false];

    protected function casts(): array
    {
        return ['locked' => 'boolean', 'published_at' => 'datetime', 'status' => LmsStatus::class];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(DiscussionPost::class, 'discussion_id');
    }
}
