<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Modules\Lms\Services\LmsDashboardService;
use App\Modules\Lms\Services\ProgressService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsDashboardController extends BaseController
{
    public function __construct(
        private readonly LmsDashboardService $dashboard,
        private readonly ProgressService $progress,
        private readonly LmsAuthorizationService $auth,
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->ok($this->dashboard->forUser($request->user()));
    }

    /** Operational learning progress for an authorized student. */
    public function progress(Request $request): JsonResponse
    {
        $studentId = $request->integer('student_id');
        $this->auth->authorizeStudent($request->user(), $studentId);

        return $this->ok($this->progress->forStudent($studentId));
    }
}
