<?php

declare(strict_types=1);

namespace App\Modules\Staff\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Staff\Models\Staff;
use App\Platform\Shared\Policies\BasePolicy;

class StaffPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('staff.view');
    }

    public function view(User $actor, Staff $staff): bool
    {
        return $actor->hasPermission('staff.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('staff.create');
    }

    public function update(User $actor, Staff $staff): bool
    {
        return $actor->hasPermission('staff.edit');
    }

    public function delete(User $actor, Staff $staff): bool
    {
        return $actor->hasPermission('staff.delete');
    }
}
