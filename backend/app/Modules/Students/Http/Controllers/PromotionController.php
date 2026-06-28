<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Actions\PromoteStudentAction;
use App\Modules\Students\Http\Requests\PromotionRequest;
use App\Modules\Students\Http\Resources\StudentResource;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class PromotionController extends BaseController
{
    /** Promote a student: creates a new academic record (history is immutable). */
    public function store(PromotionRequest $request, int|string $id, PromoteStudentAction $action): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        /** @var array{academic_year_id:int, class_id:int} $data */
        $data = $request->validated();

        return $this->ok(
            new StudentResource($action->handle($student, $data)->load('currentRecord')),
            'Student promoted.',
            201,
        );
    }
}
