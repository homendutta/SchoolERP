<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Resources\AcademicRecordResource;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only access to the immutable academic history. "Current" is derived as
 * the latest record (never a flag mutated on old records).
 */
class AcademicRecordController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['student_id' => ['required', 'integer']]);

        $records = StudentAcademicRecord::query()
            ->with(['schoolClass:id,name', 'section:id,name', 'academicYear:id,name'])
            ->where('student_id', $request->integer('student_id'))
            ->orderByDesc('id')
            ->get();

        // The latest record is the current placement, unless it has been closed
        // (external transfer / withdrawal set ended_on).
        $latest = $records->first();
        $records->each(function (StudentAcademicRecord $record) use ($latest): void {
            $record->setAttribute('is_current_marked', $latest !== null && $record->id === $latest->id && $record->ended_on === null);
        });

        return $this->ok(AcademicRecordResource::collection($records));
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new AcademicRecordResource(
            StudentAcademicRecord::query()->with(['schoolClass:id,name', 'section:id,name', 'academicYear:id,name'])->findOrFail($id)
        ));
    }
}
