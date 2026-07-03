<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Payroll\Enums\PayrollRunStatus;
use App\Modules\Payroll\Models\PayrollRun;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Payroll runs. Number from the Number Generator; immutable once locked. */
class PayrollRunService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return PayrollRun::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->withCount('payslips');
    }

    protected function filterable(): array
    {
        return ['school_id', 'period_year', 'period_month', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'period_year', 'period_month', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => PayrollRunStatus::class],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['run_number'])) {
                $data['run_number'] = $this->numbers->next('payroll_run', $data['school_id'] ?? null);
            }

            return PayrollRun::query()->create($data);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        if ($model instanceof PayrollRun && $model->isLocked()) {
            throw BusinessRuleException::make('A locked payroll run cannot be edited. Create a new run for corrections.', 'PAYROLL_LOCKED');
        }

        return parent::update($model, $data);
    }
}
