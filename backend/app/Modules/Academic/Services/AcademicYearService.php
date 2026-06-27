<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\AcademicYear;
use App\Platform\Shared\Services\BaseCrudService;

class AcademicYearService extends BaseCrudService
{
    protected function model(): string
    {
        return AcademicYear::class;
    }

    protected function searchable(): array
    {
        return ['name', 'short_name', 'slug'];
    }

    protected function filterable(): array
    {
        return ['status', 'is_current', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'start_date', 'sort_order', 'created_at'];
    }

    /** Make a year the only current one for its school. */
    public function setCurrent(AcademicYear $year): AcademicYear
    {
        return $this->transaction(function () use ($year): AcademicYear {
            AcademicYear::query()
                ->where('school_id', $year->school_id)
                ->where('id', '!=', $year->id)
                ->update(['is_current' => false]);

            $year->forceFill(['is_current' => true])->save();

            return $year->refresh();
        });
    }
}
