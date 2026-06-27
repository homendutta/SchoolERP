<?php

declare(strict_types=1);

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Administration\Models\User;
use App\Platform\Shared\Policies\BasePolicy;

class ClassPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('academic.classes.view');
    }

    public function view(User $actor, SchoolClass $class): bool
    {
        return $actor->hasPermission('academic.classes.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('academic.classes.create');
    }

    public function update(User $actor, SchoolClass $class): bool
    {
        return $actor->hasPermission('academic.classes.edit');
    }

    public function delete(User $actor, SchoolClass $class): bool
    {
        return $actor->hasPermission('academic.classes.delete');
    }
}
