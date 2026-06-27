<?php

declare(strict_types=1);

namespace App\Modules\Administration\Policies;

use App\Modules\Administration\Models\User;
use App\Platform\Shared\Policies\BasePolicy;

/**
 * Authorizes actions on User records via the RBAC permission slugs. Super admin
 * is allowed everything (handled inside hasPermission()).
 */
class UserPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('users.view');
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->hasPermission('users.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('users.create');
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->hasPermission('users.edit');
    }

    public function delete(User $actor, User $target): bool
    {
        // A user can never delete their own account.
        if ($actor->id === $target->id) {
            return false;
        }

        return $actor->hasPermission('users.delete');
    }
}
