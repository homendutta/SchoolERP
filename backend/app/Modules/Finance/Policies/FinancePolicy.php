<?php

declare(strict_types=1);

namespace App\Modules\Finance\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Finance\Models\StudentFee;
use App\Platform\Shared\Policies\BasePolicy;

class FinancePolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('finance.view');
    }

    public function view(User $actor, StudentFee $fee): bool
    {
        return $actor->hasPermission('finance.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('finance.manage');
    }

    public function collect(User $actor): bool
    {
        return $actor->hasPermission('finance.collect');
    }

    public function refund(User $actor): bool
    {
        return $actor->hasPermission('finance.refund');
    }
}
