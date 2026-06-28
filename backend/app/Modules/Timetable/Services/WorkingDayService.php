<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\Weekday;
use App\Modules\Timetable\Models\TimetableWorkingDay;
use App\Platform\Shared\Services\BaseCrudService;

class WorkingDayService extends BaseCrudService
{
    protected function model(): string
    {
        return TimetableWorkingDay::class;
    }

    protected function filterable(): array
    {
        return ['school_id', 'is_working'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'weekday'];
    }

    /**
     * Upsert a school's working-day configuration in one call.
     *
     * @param  array<int, array{weekday:string, is_working?:bool}>  $days
     * @return array<int, TimetableWorkingDay>
     */
    public function sync(int $schoolId, array $days): array
    {
        return $this->transaction(function () use ($schoolId, $days): array {
            $result = [];
            foreach ($days as $day) {
                $weekday = Weekday::from((string) $day['weekday']);
                $result[] = TimetableWorkingDay::query()->updateOrCreate(
                    ['school_id' => $schoolId, 'weekday' => $weekday->value],
                    ['is_working' => (bool) ($day['is_working'] ?? true), 'sort_order' => $weekday->sortOrder()],
                );
            }

            return $result;
        });
    }
}
