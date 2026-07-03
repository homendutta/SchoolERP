<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A quiz question with options + correct answer(s). */
class QuizQuestion extends Model
{
    protected $table = 'lms_quiz_questions';

    protected $fillable = ['quiz_id', 'type', 'question', 'options', 'answer', 'marks', 'sequence'];

    protected function casts(): array
    {
        return ['options' => 'array', 'answer' => 'array', 'marks' => 'decimal:2', 'sequence' => 'integer', 'type' => QuestionType::class];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
