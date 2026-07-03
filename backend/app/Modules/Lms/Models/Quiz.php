<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\LmsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An LMS learning quiz (NOT an Examination exam). */
class Quiz extends Model
{
    use SoftDeletes;

    protected $table = 'lms_quizzes';

    protected $fillable = [
        'school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id', 'title', 'description',
        'time_limit', 'passing_marks', 'random_order', 'immediate_result', 'max_attempts', 'status', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'draft', 'random_order' => false, 'immediate_result' => true, 'max_attempts' => 1];

    protected function casts(): array
    {
        return [
            'time_limit' => 'integer', 'passing_marks' => 'decimal:2', 'random_order' => 'boolean',
            'immediate_result' => 'boolean', 'max_attempts' => 'integer', 'published_at' => 'datetime', 'status' => LmsStatus::class,
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('sequence');
    }
}
