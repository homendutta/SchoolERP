<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use Illuminate\Database\Eloquent\Model;

/** Marks a lesson completed by a student (operational progress). */
class LessonCompletion extends Model
{
    protected $table = 'lms_lesson_completions';

    protected $fillable = ['school_id', 'lesson_id', 'student_id', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
