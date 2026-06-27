<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\Models\SubjectGroup;
use App\Modules\Academic\Services\SubjectGroupService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateSubjectGroupAction implements Action
{
    use AsAction;

    public function __construct(private readonly SubjectGroupService $service) {}

    /** @param array<string, mixed> $data validated data (may include subject_ids[]) */
    public function handle(array $data): SubjectGroup
    {
        /** @var SubjectGroup $group */
        $group = $this->service->create($data);

        return $group;
    }
}
