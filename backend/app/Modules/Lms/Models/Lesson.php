<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A lesson within a plan: rich text, Media attachments, links, embedded videos. */
class Lesson extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'lms_lessons';

    protected $fillable = [
        'school_id', 'lesson_plan_id', 'title', 'body', 'attachments', 'external_links',
        'embedded_videos', 'estimated_duration', 'reading_time', 'status', 'scheduled_at', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'attachments' => 'array', 'external_links' => 'array', 'embedded_videos' => 'array',
            'estimated_duration' => 'integer', 'reading_time' => 'integer',
            'scheduled_at' => 'datetime', 'published_at' => 'datetime', 'status' => LmsStatus::class,
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class, 'lesson_plan_id');
    }
}
