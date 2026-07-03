<?php

declare(strict_types=1);

namespace App\Modules\Cms\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Cms\Models\Page;
use App\Platform\Shared\Policies\BasePolicy;

class CmsPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('cms.view');
    }

    public function view(User $actor, Page $page): bool
    {
        return $actor->hasPermission('cms.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('cms.manage');
    }
}
