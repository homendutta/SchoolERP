<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Platform\Shared\Policies\BasePolicy;

class TimetablePolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('timetable.view');
    }

    public function view(User $actor, ClassTimetable $entry): bool
    {
        return $actor->hasPermission('timetable.view');
    }

    public function manage(User $actor): bool
    {
        return $actor->hasPermission('timetable.manage');
    }

    public function substitute(User $actor): bool
    {
        return $actor->hasPermission('timetable.substitute');
    }
}
