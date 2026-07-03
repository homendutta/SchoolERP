<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\HumanResources\Models\Designation;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Designations (hierarchy + grade). The designation code is issued by the Number Generator. */
class DesignationService extends BaseCrudService
{
    public function __construct(private readonly NumberGeneratorService $numbers) {}

    protected function model(): string
    {
        return Designation::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['department:id,name', 'parent:id,name']);
    }

    protected function searchable(): array
    {
        return ['name', 'code', 'grade'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'department_id', 'parent_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['code'])) {
                $data['code'] = $this->numbers->next('hr_designation', $data['school_id'] ?? null);
            }

            return Designation::query()->create($data);
        });
    }
}
