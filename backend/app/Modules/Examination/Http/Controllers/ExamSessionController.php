<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Actions\AssignSubjectsAction;
use App\Modules\Examination\Actions\ProcessResultsAction;
use App\Modules\Examination\Actions\PublishResultsAction;
use App\Modules\Examination\Http\Requests\ExamSessionRequest;
use App\Modules\Examination\Http\Resources\ExamSessionResource;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Services\ExamSessionService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExamSessionController extends BaseCrudController
{
    public function __construct(private readonly ExamSessionService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamSessionResource::class;
    }

    public function store(ExamSessionRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(ExamSessionRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Auto-assign core subjects to current students of each mapped class. */
    public function assignSubjects(int|string $id, AssignSubjectsAction $action): JsonResponse
    {
        return $this->ok($action->handle($this->session($id)), 'Core subjects assigned.');
    }

    /** Process results (grading + ranking) for the session. */
    public function process(int|string $id, ProcessResultsAction $action): JsonResponse
    {
        return $this->ok($action->handle($this->session($id)), 'Results processed.');
    }

    /** Publish results (audit + per-student timeline). */
    public function publish(int|string $id, PublishResultsAction $action): JsonResponse
    {
        return $this->ok($action->handle($this->session($id)), 'Results published.');
    }

    private function session(int|string $id): ExamSession
    {
        return ExamSession::query()->findOrFail($id);
    }
}
