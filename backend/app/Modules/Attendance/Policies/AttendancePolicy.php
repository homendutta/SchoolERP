<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Platform\Shared\Policies\BasePolicy;

class AttendancePolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('attendance.view');
    }

    public function view(User $actor, AttendanceRecord $record): bool
    {
        return $actor->hasPermission('attendance.view');
    }

    public function mark(User $actor): bool
    {
        return $actor->hasPermission('attendance.mark');
    }

    public function correct(User $actor, AttendanceRecord $record): bool
    {
        return $actor->hasPermission('attendance.correct');
    }
}
