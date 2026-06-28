<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamGrade;

/** Maps a percentage to a configured grade (never hardcoded). */
class GradeResolver
{
    public function resolve(int $schoolId, float $percentage): ?ExamGrade
    {
        return ExamGrade::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('min_percentage')
            ->first();
    }
}
