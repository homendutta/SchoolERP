<?php

declare(strict_types=1);

namespace App\Modules\Academic\Policies;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Administration\Models\User;
use App\Platform\Shared\Policies\BasePolicy;

class AcademicYearPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('academic.years.view');
    }

    public function view(User $actor, AcademicYear $year): bool
    {
        return $actor->hasPermission('academic.years.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('academic.years.create');
    }

    public function update(User $actor, AcademicYear $year): bool
    {
        return $actor->hasPermission('academic.years.edit');
    }

    public function delete(User $actor, AcademicYear $year): bool
    {
        return $actor->hasPermission('academic.years.delete');
    }
}
