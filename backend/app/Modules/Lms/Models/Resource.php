<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A reusable classroom resource (notes, worksheets, reading lists, ...). */
class Resource extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'lms_resources';

    protected $fillable = [
        'school_id', 'subject_id', 'class_id', 'teacher_id', 'title', 'topic',
        'type', 'body', 'media_id', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'status' => LmsStatus::class];
    }
}
