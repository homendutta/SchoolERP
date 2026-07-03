<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Payroll\Models\PayrollRun;
use App\Platform\Shared\Policies\BasePolicy;

class PayrollPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('payroll.view');
    }

    public function view(User $actor, PayrollRun $run): bool
    {
        return $actor->hasPermission('payroll.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('payroll.manage');
    }

    public function process(User $actor): bool
    {
        return $actor->hasPermission('payroll.process');
    }
}
