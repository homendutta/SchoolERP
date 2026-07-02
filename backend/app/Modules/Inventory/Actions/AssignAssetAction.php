<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Actions;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Services\AssignmentEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/** Assign an asset to a target (historical, never overwriting). */
class AssignAssetAction implements Action
{
    use AsAction;

    public function __construct(private readonly AssignmentEngine $engine) {}

    /**
     * @param  array{asset_id:int, target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null}  $payload
     */
    public function handle(array $payload): AssetAssignment
    {
        $asset = Asset::query()->findOrFail($payload['asset_id']);

        return $this->engine->assign($asset, $payload, Auth::id());
    }
}
