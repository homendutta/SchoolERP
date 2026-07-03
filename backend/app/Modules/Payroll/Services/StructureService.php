<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\Structure;
use App\Modules\Payroll\Models\StructureComponent;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Reusable, versioned salary structures with their components. */
class StructureService extends BaseCrudService
{
    protected function model(): string
    {
        return Structure::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['components.component:id,name,code,component_type,calculation_type,default_value']);
    }

    protected function searchable(): array
    {
        return ['name', 'grade'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'effective_date'];
    }

    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $components = $data['components'] ?? [];
            unset($data['components']);

            $structure = Structure::query()->create($data);
            $this->syncComponents((int) $structure->id, is_array($components) ? $components : []);

            return $structure->load('components');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            $components = $data['components'] ?? null;
            unset($data['components']);

            $model->fill($data)->save();

            if (is_array($components)) {
                StructureComponent::query()->where('structure_id', $model->getKey())->delete();
                $this->syncComponents((int) $model->getKey(), $components);
            }

            return $model->load('components');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     */
    private function syncComponents(int $structureId, array $components): void
    {
        foreach (array_values($components) as $i => $row) {
            StructureComponent::query()->create([
                'structure_id' => $structureId,
                'component_id' => $row['component_id'],
                'value' => $row['value'] ?? null,
                'sequence' => $row['sequence'] ?? $i,
            ]);
        }
    }
}
