<?php

declare(strict_types=1);

namespace App\Modules\Students\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Policies\BasePolicy;

class StudentPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('students.view');
    }

    public function view(User $actor, Student $student): bool
    {
        return $actor->hasPermission('students.view');
    }

    public function update(User $actor, Student $student): bool
    {
        return $actor->hasPermission('students.edit');
    }

    public function promote(User $actor, Student $student): bool
    {
        return $actor->hasPermission('students.promote');
    }

    public function transfer(User $actor, Student $student): bool
    {
        return $actor->hasPermission('students.transfer');
    }

    public function withdraw(User $actor, Student $student): bool
    {
        return $actor->hasPermission('students.withdraw');
    }
}
