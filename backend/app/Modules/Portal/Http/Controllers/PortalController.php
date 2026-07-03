<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Modules\Portal\Services\PortalContextService;
use App\Modules\Portal\Services\PortalDashboardService;
use App\Modules\Portal\Services\PortalDataService;
use App\Modules\Portal\Services\PortalProfileService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Portal read endpoints. Every student-scoped call is authorized through
 * PortalContextService (parents → children, students → self, teachers → their
 * responsibilities) and then delegates to PortalDataService, which reads from the
 * owning module. The portal computes nothing itself.
 */
class PortalController extends BaseController
{
    public function __construct(
        private readonly PortalContextService $context,
        private readonly PortalDataService $data,
        private readonly PortalDashboardService $dashboard,
        private readonly PortalProfileService $profile,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->ok($this->dashboard->forUser($request->user()));
    }

    public function attendance(Request $request): JsonResponse
    {
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->attendance((int) $student->id));
    }

    public function examinations(Request $request): JsonResponse
    {
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));
        $sessionId = $request->integer('session_id') ?: null;

        return $this->ok($this->data->examinations((int) $student->id, $sessionId));
    }

    public function library(Request $request): JsonResponse
    {
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->library((int) $student->id));
    }

    public function transport(Request $request): JsonResponse
    {
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->transport((int) $student->id));
    }

    public function hostel(Request $request): JsonResponse
    {
        $student = $this->context->authorizeStudent($request->user(), $request->integer('student_id'));

        return $this->ok($this->data->hostel((int) $student->id));
    }

    public function timetable(Request $request): JsonResponse
    {
        $user = $request->user();
        $ctx = $this->context->resolve($user);

        if ($ctx->staff !== null) {
            $academicYearId = $request->integer('academic_year_id');

            return $this->ok($this->data->teacherTimetable((int) $ctx->staff->id, $academicYearId));
        }

        $student = $this->context->authorizeStudent($user, $request->integer('student_id'));

        return $this->ok($this->data->studentTimetable((int) $student->id));
    }

    public function messages(Request $request): JsonResponse
    {
        return $this->ok($this->data->messages((int) $request->user()->school_id));
    }

    public function downloads(Request $request): JsonResponse
    {
        return $this->ok($this->data->downloads((int) $request->user()->school_id));
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->ok($this->profile->show($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        return $this->ok($this->profile->update($request->user(), $validated), 'Profile updated.');
    }
}
