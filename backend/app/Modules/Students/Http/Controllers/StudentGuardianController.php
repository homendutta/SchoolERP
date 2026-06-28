<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Requests\GuardianLinkRequest;
use App\Modules\Students\Http\Resources\StudentResource;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentGuardianService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Manages the Student ↔ Guardian relationship (the pivot). Relationship type is
 * Master Data; only one primary guardian per student is enforced by the service.
 */
class StudentGuardianController extends BaseController
{
    public function __construct(private readonly StudentGuardianService $service) {}

    public function store(GuardianLinkRequest $request, int|string $id): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        $data = $request->validated();
        $guardianId = (int) $data['guardian_id'];
        unset($data['guardian_id']);

        $student = $this->service->link($student, $guardianId, $data);

        return $this->ok(new StudentResource($student->load('guardians')), 'Guardian linked.', 201);
    }

    public function update(GuardianLinkRequest $request, int|string $id, int|string $guardianId): JsonResponse
    {
        $student = Student::query()->findOrFail($id);

        $student = $this->service->link($student, (int) $guardianId, $request->validated());

        return $this->ok(new StudentResource($student->load('guardians')), 'Guardian relationship updated.');
    }

    public function destroy(int|string $id, int|string $guardianId): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        $this->service->unlink($student, (int) $guardianId);

        return $this->ok(null, 'Guardian unlinked.');
    }
}
