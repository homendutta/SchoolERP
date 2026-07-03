<?php

declare(strict_types=1);

namespace App\Modules\Lms\Enums;

/** LMS quiz question types (learning quizzes — NOT Examination exams). */
enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case FillBlank = 'fill_blank';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
