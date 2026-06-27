<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Services\AcademicYearService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class UpdateAcademicYearAction implements Action
{
    use AsAction;

    public function __construct(private readonly AcademicYearService $service) {}

    /** @param array<string, mixed> $data validated partial data (may include version) */
    public function handle(AcademicYear $year, array $data): AcademicYear
    {
        /** @var AcademicYear $updated */
        $updated = $this->service->update($year, $data);

        return $updated;
    }
}
