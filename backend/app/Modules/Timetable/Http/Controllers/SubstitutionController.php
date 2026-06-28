<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Actions\CreateSubstitutionAction;
use App\Modules\Timetable\Http\Requests\SubstitutionRequest;
use App\Modules\Timetable\Http\Resources\SubstitutionResource;
use App\Modules\Timetable\Services\SubstitutionService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/**
 * Substitutions are SEPARATE records — the master timetable is never modified.
 * Creation goes through the action (audit log + teacher timelines).
 */
class SubstitutionController extends BaseCrudController
{
    public function __construct(private readonly SubstitutionService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SubstitutionResource::class;
    }

    public function store(SubstitutionRequest $request, CreateSubstitutionAction $action): JsonResponse
    {
        $substitution = $action->handle($request->validated());

        return $this->ok(new SubstitutionResource($substitution->load([
            'originalTeacher:id,name', 'substituteTeacher:id,name', 'period:id,name',
        ])), 'Substitution recorded.', 201);
    }

    public function update(SubstitutionRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }
}
