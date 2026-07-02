<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Administration\Services\NumberGeneratorService;
use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Models\Asset;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Physical assets. Asset number from the Number Generator; on create the
 * HasIdentity trait issues the asset's permanent Identity (barcode + QR).
 */
class AssetService extends BaseCrudService
{
    public function __construct(
        private readonly NumberGeneratorService $numbers,
        private readonly AssetLifecycleService $lifecycle,
    ) {}

    protected function model(): string
    {
        return Asset::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'assetModel:id,name,brand',
            'category:id,name',
            'vendor:id,name',
            'assetIdentity:id,identity_number,public_identifier,barcode_value,qr_payload',
        ]);
    }

    protected function searchable(): array
    {
        return ['asset_number', 'serial_number'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_model_id', 'category_id', 'vendor_id', 'status', 'condition'];
    }

    protected function sortable(): array
    {
        return ['id', 'asset_number', 'purchase_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'asset_number' => ['type' => 'text', 'columns' => ['asset_number']],
            'serial_number' => ['type' => 'text', 'columns' => ['serial_number']],
            'status' => ['type' => 'enum', 'enum' => AssetStatus::class],
            'barcode' => ['type' => 'relation', 'relation' => 'assetIdentity', 'columns' => ['identity_number', 'barcode_value']],
            'model' => ['type' => 'relation', 'relation' => 'assetModel', 'columns' => ['name', 'brand']],
            'vendor' => ['type' => 'relation', 'relation' => 'vendor', 'columns' => ['name']],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            if (empty($data['asset_number'])) {
                $data['asset_number'] = $this->numbers->next('inventory.asset', (int) $data['school_id'], Auth::id());
            } else {
                $this->numbers->reserve('inventory.asset', (string) $data['asset_number'], (int) $data['school_id'], Auth::id());
            }

            $asset = Asset::query()->create($data);
            $this->lifecycle->recordInitial($asset);

            return $asset;
        });
    }

    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }
}
