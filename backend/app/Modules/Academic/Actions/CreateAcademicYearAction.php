<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\DTO\AcademicYearData;
use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Services\AcademicYearService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateAcademicYearAction implements Action
{
    use AsAction;

    public function __construct(private readonly AcademicYearService $service) {}

    public function handle(AcademicYearData $data): AcademicYear
    {
        /** @var AcademicYear $year */
        $year = $this->service->create($data->filled());

        return $year;
    }
}
