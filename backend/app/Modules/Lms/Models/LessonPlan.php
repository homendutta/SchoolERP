<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A lesson plan for an assigned (class, section, subject). History preserved. */
class LessonPlan extends Model
{
    use SoftDeletes;

    protected $table = 'lms_lesson_plans';

    protected $fillable = [
        'school_id', 'academic_year_id', 'class_id', 'section_id', 'subject_id', 'teacher_id',
        'title', 'objectives', 'topics', 'teaching_method', 'planned_date', 'completion_status',
        'notes', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'completion_status' => 'planned'];

    protected function casts(): array
    {
        return ['planned_date' => 'date', 'published_at' => 'datetime', 'status' => LmsStatus::class];
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'lesson_plan_id');
    }
}
