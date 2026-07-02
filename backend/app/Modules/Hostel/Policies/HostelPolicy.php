<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Hostel\Models\Hostel;
use App\Platform\Shared\Policies\BasePolicy;

class HostelPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('hostel.view');
    }

    public function view(User $actor, Hostel $hostel): bool
    {
        return $actor->hasPermission('hostel.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('hostel.manage');
    }

    public function allocate(User $actor): bool
    {
        return $actor->hasPermission('hostel.allocate');
    }
}
