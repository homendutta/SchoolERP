<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Actions\EnrollStudentAction;
use App\Modules\Admissions\Http\Resources\ApplicationResource;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends BaseController
{
    public function __construct(private readonly EnrollStudentAction $action) {}

    /**
     * Enroll an approved application: creates Guardian, Student, Academic Record,
     * users + roles, admission number, and credentials — all in one transaction.
     */
    public function enroll(int|string $id): JsonResponse
    {
        $application = AdmissionApplication::query()->findOrFail($id);
        $result = $this->action->handle($application);

        return $this->ok([
            'application' => new ApplicationResource($result->application),
            'student' => [
                'id' => $result->student->id,
                'admission_number' => $result->student->admission_number,
                'name' => $result->student->name,
            ],
            'guardian' => [
                'id' => $result->guardian->id,
                'parent_number' => $result->guardian->parent_number,
                'name' => $result->guardian->name,
            ],
            'credentials' => [
                'student' => $result->studentCredentials,
                'parent' => $result->parentCredentials,
            ],
        ], 'Student enrolled.', 201);
    }
}
