<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Actions\TransferStudentAction;
use App\Modules\Students\Http\Requests\TransferRequest;
use App\Modules\Students\Http\Resources\TransferResource;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentTransfer;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $rows = StudentTransfer::query()
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->latest('id')
            ->get();

        return $this->ok(TransferResource::collection($rows));
    }

    public function store(TransferRequest $request, int|string $id, TransferStudentAction $action): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        /** @var array{type:string, transfer_date:string} $data */
        $data = $request->validated();

        return $this->ok(new TransferResource($action->handle($student, $data)), 'Transfer recorded.', 201);
    }
}
