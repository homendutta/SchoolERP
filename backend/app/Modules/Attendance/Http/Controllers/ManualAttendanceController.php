<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Actions\CorrectAttendanceAction;
use App\Modules\Attendance\Actions\MarkAttendanceAction;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Http\Requests\CorrectionRequest;
use App\Modules\Attendance\Http\Requests\ManualAttendanceRequest;
use App\Modules\Attendance\Http\Resources\AttendanceRecordResource;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class ManualAttendanceController extends BaseController
{
    /** Bulk manual marking (students or staff). */
    public function store(ManualAttendanceRequest $request, MarkAttendanceAction $action): JsonResponse
    {
        /** @var array{date:string, entries:array<int, array<string, mixed>>} $data */
        $data = $request->validated();

        return $this->ok($action->handle($data), 'Attendance marked.', 201);
    }

    /** Authorized correction of a single record (audited + timeline). */
    public function correct(CorrectionRequest $request, int|string $id, CorrectAttendanceAction $action): JsonResponse
    {
        $record = AttendanceRecord::query()->findOrFail($id);
        $status = AttendanceStatus::from((string) $request->validated('status'));

        $updated = $action->handle($record, $status, $request->validated('remarks'));

        return $this->ok(new AttendanceRecordResource($updated->load(['identity', 'owner', 'session'])), 'Attendance corrected.');
    }
}
