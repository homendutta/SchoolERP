<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Enums\MaterialType;
use App\Platform\Shared\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A learning material (the file lives in the Media Platform). */
class Material extends Model
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'lms_materials';

    protected $fillable = [
        'school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id', 'title',
        'description', 'type', 'media_id', 'topic', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'type' => 'pdf'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'type' => MaterialType::class, 'status' => LmsStatus::class];
    }
}
