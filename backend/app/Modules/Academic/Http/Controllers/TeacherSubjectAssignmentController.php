<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Http\Requests\TeacherSubjectAssignmentRequest;
use App\Modules\Academic\Http\Resources\TeacherSubjectAssignmentResource;
use App\Modules\Academic\Services\TeacherSubjectAssignmentService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TeacherSubjectAssignmentController extends BaseCrudController
{
    public function __construct(private readonly TeacherSubjectAssignmentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return TeacherSubjectAssignmentResource::class;
    }

    public function store(TeacherSubjectAssignmentRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TeacherSubjectAssignmentRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
