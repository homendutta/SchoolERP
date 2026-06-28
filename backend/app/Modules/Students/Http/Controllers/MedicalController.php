<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Requests\StudentMedicalRequest;
use App\Modules\Students\Http\Resources\StudentResource;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentMedicalService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class MedicalController extends BaseController
{
    public function __construct(private readonly StudentMedicalService $service) {}

    public function update(StudentMedicalRequest $request, int|string $id): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        $updated = $this->service->update($student, $request->validated());

        return $this->ok(new StudentResource($updated->load('bloodGroup')), 'Medical information updated.');
    }
}
