<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Enums\RankingMethod;
use App\Modules\Examination\Models\ExamResult;

/**
 * Configurable ranking. Ranks are computed per class+section, ordered by
 * percentage. Schools choose dense, competition or no ranking.
 */
class RankingService
{
    public function apply(int $sessionId, RankingMethod $method): void
    {
        if ($method === RankingMethod::None) {
            ExamResult::query()->where('exam_session_id', $sessionId)->update(['rank' => null]);

            return;
        }

        $groups = ExamResult::query()
            ->where('exam_session_id', $sessionId)
            ->where('result_status', '!=', 'pending')
            ->get()
            ->groupBy(fn (ExamResult $r) => $r->class_id.'-'.($r->section_id ?? '0'));

        foreach ($groups as $rows) {
            $sorted = $rows->sortByDesc('percentage')->values();

            $rank = 0;
            $position = 0;
            $previous = null;
            foreach ($sorted as $row) {
                $position++;
                if ($previous === null || (float) $row->percentage < $previous) {
                    // dense: rank increments by 1; competition: jumps to position
                    $rank = $method === RankingMethod::Dense ? $rank + 1 : $position;
                    $previous = (float) $row->percentage;
                }
                $row->update(['rank' => $rank]);
            }
        }
    }
}
