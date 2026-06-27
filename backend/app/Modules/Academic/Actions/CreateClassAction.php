<?php

declare(strict_types=1);

namespace App\Modules\Academic\Actions;

use App\Modules\Academic\DTO\ClassData;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Services\SchoolClassService;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

class CreateClassAction implements Action
{
    use AsAction;

    public function __construct(private readonly SchoolClassService $service) {}

    public function handle(ClassData $data): SchoolClass
    {
        /** @var SchoolClass $class */
        $class = $this->service->create($data->filled());

        return $class;
    }
}
