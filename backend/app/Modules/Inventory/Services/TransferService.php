<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\AssetTransfer;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read over asset transfer events. */
class TransferService extends BaseCrudService
{
    protected function model(): string
    {
        return AssetTransfer::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['asset:id,asset_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'transfer_date', 'created_at'];
    }
}
