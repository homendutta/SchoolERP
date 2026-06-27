<?php

declare(strict_types=1);

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\Subject;
use App\Modules\Administration\Models\User;
use App\Platform\Shared\Policies\BasePolicy;

class SubjectPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('academic.subjects.view');
    }

    public function view(User $actor, Subject $subject): bool
    {
        return $actor->hasPermission('academic.subjects.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('academic.subjects.create');
    }

    public function update(User $actor, Subject $subject): bool
    {
        return $actor->hasPermission('academic.subjects.edit');
    }

    public function delete(User $actor, Subject $subject): bool
    {
        return $actor->hasPermission('academic.subjects.delete');
    }
}
