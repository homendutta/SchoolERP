<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Services\AcademicYearService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class SetCurrentAcademicYearAction implements Action
{
    use AsAction;

    public function __construct(private readonly AcademicYearService $service) {}

    public function handle(AcademicYear $year): AcademicYear
    {
        return $this->service->setCurrent($year);
    }
}
