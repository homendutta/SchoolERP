<?php

declare(strict_types=1);

namespace App\Modules\Library\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Library\Models\Borrowing;
use App\Platform\Shared\Policies\BasePolicy;

class LibraryPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('library.view');
    }

    public function view(User $actor, Borrowing $borrowing): bool
    {
        return $actor->hasPermission('library.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('library.manage');
    }

    public function circulate(User $actor): bool
    {
        return $actor->hasPermission('library.circulate');
    }
}
