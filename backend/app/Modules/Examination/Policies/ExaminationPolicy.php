<?php

declare(strict_types=1);

namespace App\Modules\Examination\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Examination\Models\ExamSession;
use App\Platform\Shared\Policies\BasePolicy;

class ExaminationPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('examinations.view');
    }

    public function view(User $actor, ExamSession $session): bool
    {
        return $actor->hasPermission('examinations.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('examinations.manage');
    }

    public function marks(User $actor): bool
    {
        return $actor->hasPermission('examinations.marks');
    }

    public function publish(User $actor): bool
    {
        return $actor->hasPermission('examinations.publish');
    }
}
