<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Controllers;

use App\Modules\Academic\Actions\AssignClassTeacherAction;
use App\Modules\Academic\Http\Requests\ClassTeacherRequest;
use App\Modules\Academic\Http\Resources\ClassTeacherResource;
use App\Modules\Academic\Models\ClassTeacher;
use App\Modules\Academic\Services\ClassTeacherService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassTeacherController extends BaseController
{
    public function __construct(private readonly ClassTeacherService $service) {}

    /** List class-teacher assignments (active by default; pass all=1 for history). */
    public function index(Request $request): JsonResponse
    {
        $query = ClassTeacher::query()->with('teacher:id,name');

        foreach (['academic_year_id', 'class_id', 'section_id', 'teacher_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->integer($filter));
            }
        }

        if (! $request->boolean('all')) {
            $query->where('is_active', true);
        }

        $rows = $query->latest()->get();

        return $this->ok(ClassTeacherResource::collection($rows));
    }

    /** Assign a class teacher (supersedes the current active one). */
    public function store(ClassTeacherRequest $request, AssignClassTeacherAction $action): JsonResponse
    {
        /** @var array{academic_year_id:int, class_id:int, section_id:int, teacher_id:int} $data */
        $data = $request->validated();
        $assignment = $action->handle($data);

        return $this->ok(new ClassTeacherResource($assignment), 'Class teacher assigned.', 201);
    }

    /** Assignment history for a given Academic Year / Class / Section. */
    public function history(Request $request): JsonResponse
    {
        $rows = $this->service->history(
            $request->integer('academic_year_id'),
            $request->integer('class_id'),
            $request->integer('section_id'),
        );

        return $this->ok(ClassTeacherResource::collection($rows));
    }
}
