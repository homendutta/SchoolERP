<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Actions\CopyTimetableAction;
use App\Modules\Timetable\Http\Requests\CopyTimetableRequest;
use App\Modules\Timetable\Http\Requests\TemplateRequest;
use App\Modules\Timetable\Http\Resources\TemplateResource;
use App\Modules\Timetable\Services\TemplateService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class TemplateController extends BaseCrudController
{
    public function __construct(private readonly TemplateService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return TemplateResource::class;
    }

    public function store(TemplateRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(TemplateRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Copy a timetable from one academic year / template into another. */
    public function copy(CopyTimetableRequest $request, CopyTimetableAction $action): JsonResponse
    {
        /** @var array{school_id:int, from_academic_year_id:int, to_academic_year_id:int, from_template_id?:int|null, to_template_id?:int|null, class_ids?:array<int, int>|null} $payload */
        $payload = $request->validated();

        return $this->ok($action->handle($payload), 'Timetable copied.');
    }
}
