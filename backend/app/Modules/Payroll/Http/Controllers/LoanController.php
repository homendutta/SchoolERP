<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Controllers;

use App\Modules\Payroll\Http\Requests\LoanRequest;
use App\Modules\Payroll\Http\Resources\SimpleResource;
use App\Modules\Payroll\Models\Loan;
use App\Modules\Payroll\Services\LoanService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class LoanController extends BaseCrudController
{
    public function __construct(private readonly LoanService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(LoanRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(LoanRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Approve a loan — activates installment deductions + notifies via the engine. */
    public function approve(int|string $id): JsonResponse
    {
        $loan = Loan::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->service->approve($loan)), 'Loan approved.');
    }
}
