<?php

declare(strict_types=1);

namespace App\Modules\Administration\Policies;

use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\User;
use App\Platform\Shared\Policies\BasePolicy;

class RolePolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('roles.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('roles.create');
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->hasPermission('roles.edit');
    }

    public function delete(User $actor, Role $role): bool
    {
        // System (default) roles cannot be deleted.
        if ($role->is_system) {
            return false;
        }

        return $actor->hasPermission('roles.delete');
    }
}
