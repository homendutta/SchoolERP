<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\FeeStructureItem;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Fee Structures combine multiple Fee Masters (items synced on save). */
class FeeStructureService extends BaseCrudService
{
    protected function model(): string
    {
        return FeeStructure::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['items.feeMaster:id,name,amount', 'schoolClass:id,name'])->withCount('items');
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'academic_year_id', 'class_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'name'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $items = $data['items'] ?? [];
            unset($data['items']);
            $structure = FeeStructure::query()->create($data);
            $this->syncItems((int) $structure->id, (array) $items);

            return $structure->load('items');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            $items = $data['items'] ?? null;
            unset($data['items']);
            $model->fill($data)->save();
            if ($items !== null) {
                $this->syncItems((int) $model->getKey(), (array) $items);
            }

            return $model->load('items');
        });
    }

    /**
     * @param  array<int, array{fee_master_id:int, amount?:float|null}>  $items
     */
    private function syncItems(int $structureId, array $items): void
    {
        FeeStructureItem::query()->where('fee_structure_id', $structureId)->delete();
        foreach (array_values($items) as $i => $item) {
            FeeStructureItem::query()->create([
                'fee_structure_id' => $structureId,
                'fee_master_id' => $item['fee_master_id'],
                'amount' => $item['amount'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}
