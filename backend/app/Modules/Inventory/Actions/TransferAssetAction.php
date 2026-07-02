<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Actions;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Services\AssignmentEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/** Transfer an asset to a new target (new records; full history preserved). */
class TransferAssetAction implements Action
{
    use AsAction;

    public function __construct(private readonly AssignmentEngine $engine) {}

    /**
     * @param  array{asset_id:int, target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null, reason?:string|null, transfer_type?:string|null}  $payload
     */
    public function handle(array $payload): AssetAssignment
    {
        $asset = Asset::query()->findOrFail($payload['asset_id']);

        return $this->engine->transfer($asset, $payload, Auth::id());
    }
}
