<?php

declare(strict_types=1);

namespace App\Modules\Transport\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Transport\Models\Vehicle;
use App\Platform\Shared\Policies\BasePolicy;

class TransportPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('transport.view');
    }

    public function view(User $actor, Vehicle $vehicle): bool
    {
        return $actor->hasPermission('transport.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('transport.manage');
    }

    public function assign(User $actor): bool
    {
        return $actor->hasPermission('transport.assign');
    }
}
