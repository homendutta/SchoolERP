<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Actions\AssignFeeStructureAction;
use App\Modules\Finance\Actions\BulkAssignFeeAction;
use App\Modules\Finance\Http\Requests\AssignFeeRequest;
use App\Modules\Finance\Http\Resources\StudentFeeResource;
use App\Modules\Finance\Models\Discount;
use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\Scholarship;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Finance\Services\ConcessionService;
use App\Modules\Finance\Services\StudentFeeService;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFeeController extends BaseController
{
    public function __construct(
        private readonly StudentFeeService $service,
        private readonly ConcessionService $concessions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(StudentFeeResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new StudentFeeResource($this->service->find($id)->load(['items', 'student:id,name,admission_number', 'schoolClass:id,name'])));
    }

    /** Assign a fee structure: individual or bulk (class / section / list). */
    public function assign(AssignFeeRequest $request, AssignFeeStructureAction $assign, BulkAssignFeeAction $bulk): JsonResponse
    {
        $data = $request->validated();
        $structure = FeeStructure::query()->findOrFail($data['structure_id']);

        if (! empty($data['bulk']) || ! empty($data['student_ids']) || ! empty($data['class_id'])) {
            return $this->ok($bulk->handle($data), 'Fee structure assigned.');
        }

        $student = Student::query()->findOrFail($data['student_id']);
        $fee = $assign->handle($student, $structure);

        return $this->ok(new StudentFeeResource($fee->load('items')), 'Fee structure assigned.', 201);
    }

    public function applyDiscount(int|string $id, Request $request): JsonResponse
    {
        $data = $request->validate(['discount_id' => ['required', 'integer', 'exists:discounts,id'], 'reason' => ['nullable', 'string']]);
        $fee = StudentFee::query()->findOrFail($id);
        $this->concessions->applyDiscount($fee, Discount::query()->findOrFail($data['discount_id']), $data['reason'] ?? null);

        return $this->ok(new StudentFeeResource($this->service->find($id)->load('items')), 'Discount applied.');
    }

    public function applyScholarship(int|string $id, Request $request): JsonResponse
    {
        $data = $request->validate(['scholarship_id' => ['required', 'integer', 'exists:scholarships,id'], 'notes' => ['nullable', 'string']]);
        $fee = StudentFee::query()->findOrFail($id);
        $this->concessions->applyScholarship($fee, Scholarship::query()->findOrFail($data['scholarship_id']), $data['notes'] ?? null);

        return $this->ok(new StudentFeeResource($this->service->find($id)->load('items')), 'Scholarship awarded.');
    }

    public function applySibling(int|string $id): JsonResponse
    {
        $fee = StudentFee::query()->findOrFail($id);
        $this->concessions->applySibling($fee);

        return $this->ok(new StudentFeeResource($this->service->find($id)->load('items')), 'Sibling concession applied.');
    }
}
