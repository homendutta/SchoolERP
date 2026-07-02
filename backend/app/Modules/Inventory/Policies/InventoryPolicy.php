<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Inventory\Models\Asset;
use App\Platform\Shared\Policies\BasePolicy;

class InventoryPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('inventory.view');
    }

    public function view(User $actor, Asset $asset): bool
    {
        return $actor->hasPermission('inventory.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('inventory.manage');
    }

    public function assign(User $actor): bool
    {
        return $actor->hasPermission('inventory.assign');
    }
}
