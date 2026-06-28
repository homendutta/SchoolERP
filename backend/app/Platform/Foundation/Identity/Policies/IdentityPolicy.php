<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Policies;

use App\Modules\Administration\Models\User;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Policies\BasePolicy;

class IdentityPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('identity.view');
    }

    public function view(User $actor, Identity $identity): bool
    {
        return $actor->hasPermission('identity.view');
    }

    public function manage(User $actor, Identity $identity): bool
    {
        return $actor->hasPermission('identity.manage');
    }
}
