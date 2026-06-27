<?php

declare(strict_types=1);

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\SubjectGroup;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubjectGroupService extends BaseCrudService
{
    protected function model(): string
    {
        return SubjectGroup::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with('subjects:id,code,name');
    }

    protected function searchable(): array
    {
        return ['code', 'name', 'slug'];
    }

    protected function filterable(): array
    {
        return ['status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'display_order', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        return $this->transaction(function () use ($data, $subjectIds): SubjectGroup {
            /** @var SubjectGroup $group */
            $group = SubjectGroup::query()->create($data);
            $group->syncSubjects((array) $subjectIds);

            return $group->load('subjects');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        $subjectIds = $data['subject_ids'] ?? null;
        unset($data['subject_ids']);

        $group = parent::update($model, $data);
        if ($subjectIds !== null && $group instanceof SubjectGroup) {
            $group->syncSubjects((array) $subjectIds);
        }

        return $group->load('subjects');
    }
}
