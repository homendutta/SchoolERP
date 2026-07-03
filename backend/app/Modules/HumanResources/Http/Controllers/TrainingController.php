<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\TrainingParticipantRequest;
use App\Modules\HumanResources\Http\Requests\TrainingRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Models\Training;
use App\Modules\HumanResources\Services\TrainingService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TrainingController extends BaseCrudController
{
    public function __construct(private readonly TrainingService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(TrainingRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TrainingRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Enrol an employee on the training programme (publishes a Communication event). */
    public function assign(TrainingParticipantRequest $request, int|string $id): JsonResponse
    {
        $training = Training::query()->findOrFail($id);
        $participant = $this->service->assignParticipant($training, (int) $request->validated()['staff_id']);

        return $this->ok(new SimpleResource($participant), 'Participant assigned.', 201);
    }
}
