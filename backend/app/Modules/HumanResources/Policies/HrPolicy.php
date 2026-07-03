<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\HumanResources\Models\EmploymentRecord;
use App\Platform\Shared\Policies\BasePolicy;

class HrPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('hr.view');
    }

    public function view(User $actor, EmploymentRecord $record): bool
    {
        return $actor->hasPermission('hr.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('hr.manage');
    }

    public function approve(User $actor): bool
    {
        return $actor->hasPermission('hr.approve');
    }
}
