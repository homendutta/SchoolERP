<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Actions\WithdrawStudentAction;
use App\Modules\Students\Http\Requests\WithdrawalRequest;
use App\Modules\Students\Http\Resources\WithdrawalResource;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentWithdrawal;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $rows = StudentWithdrawal::query()
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->latest('id')
            ->get();

        return $this->ok(WithdrawalResource::collection($rows));
    }

    public function store(WithdrawalRequest $request, int|string $id, WithdrawStudentAction $action): JsonResponse
    {
        $student = Student::query()->findOrFail($id);
        /** @var array{withdraw_date:string} $data */
        $data = $request->validated();

        return $this->ok(new WithdrawalResource($action->handle($student, $data)), 'Withdrawal recorded.', 201);
    }
}
