<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\DTO\SubjectData;
use App\Modules\Academic\Models\Subject;
use App\Modules\Academic\Services\SubjectService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateSubjectAction implements Action
{
    use AsAction;

    public function __construct(private readonly SubjectService $service) {}

    public function handle(SubjectData $data): Subject
    {
        /** @var Subject $subject */
        $subject = $this->service->create($data->filled());

        return $subject;
    }
}
