<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Requests\UpdateStudentRequest;
use App\Modules\Students\Http\Resources\StudentResource;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentService;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/**
 * Students are never created here (Admissions / import own creation). This
 * controller maintains the student: enterprise search, profile, update, and the
 * ID-card / QR preparation data.
 */
class StudentController extends BaseCrudController
{
    public function __construct(private readonly StudentService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return StudentResource::class;
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new StudentResource($this->service->profile($id)));
    }

    public function update(UpdateStudentRequest $request, int|string $id): JsonResponse
    {
        /** @var Student $student */
        $student = $this->service->find($id);

        return $this->ok(
            new StudentResource($this->service->updateProfile($student, $request->validated())),
            'Student updated.',
        );
    }

    /**
     * ID-card / QR preparation data (the designer itself is a future sprint).
     */
    public function idCard(int|string $id): JsonResponse
    {
        /** @var Student $student */
        $student = $this->service->profile($id);
        $current = $student->currentRecord;

        return $this->ok([
            'admission_number' => $student->admission_number,
            'name' => $student->name,
            'photo_url' => $student->photo_media_id ? Media::query()->find($student->photo_media_id)?->url() : null,
            'class' => $current?->schoolClass?->name,
            'section' => $current?->section?->name,
            'guardian' => $student->guardians->firstWhere('pivot.is_primary', true)?->name
                ?? $student->guardians->first()?->name,
            'blood_group' => $student->bloodGroup?->label ?? $student->blood_group,
            'qr_data' => $this->qrPayload($student),
        ]);
    }

    /** QR data only (scanning is a future concern; this exposes the payload). */
    public function qr(int|string $id): JsonResponse
    {
        /** @var Student $student */
        $student = $this->service->find($id);

        return $this->ok([
            'admission_number' => $student->admission_number,
            'qr_data' => $this->qrPayload($student),
        ]);
    }

    private function qrPayload(Student $student): string
    {
        return 'ASYLINX|'.$student->admission_number.'|'.$student->uuid;
    }
}
