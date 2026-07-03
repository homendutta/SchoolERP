<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A student attempt at an LMS quiz (timing + score + attempt number). */
class QuizAttempt extends Model
{
    protected $table = 'lms_quiz_attempts';

    protected $fillable = [
        'school_id', 'quiz_id', 'student_id', 'attempt_number', 'started_at', 'finished_at',
        'score', 'time_taken', 'passed', 'responses',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer', 'started_at' => 'datetime', 'finished_at' => 'datetime',
            'score' => 'decimal:2', 'time_taken' => 'integer', 'passed' => 'boolean', 'responses' => 'array',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
