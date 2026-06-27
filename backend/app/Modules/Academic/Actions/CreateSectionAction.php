<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Services\SectionService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateSectionAction implements Action
{
    use AsAction;

    public function __construct(private readonly SectionService $service) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Section
    {
        /** @var Section $section */
        $section = $this->service->create($data);

        return $section;
    }
}
