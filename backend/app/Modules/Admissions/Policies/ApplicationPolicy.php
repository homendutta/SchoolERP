<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Policies;

use App\Modules\Administration\Models\User;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Platform\Shared\Policies\BasePolicy;

class ApplicationPolicy extends BasePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('admissions.applications.view');
    }

    public function view(User $actor, AdmissionApplication $application): bool
    {
        return $actor->hasPermission('admissions.applications.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('admissions.applications.create');
    }

    public function update(User $actor, AdmissionApplication $application): bool
    {
        return $actor->hasPermission('admissions.applications.edit');
    }

    public function delete(User $actor, AdmissionApplication $application): bool
    {
        return $actor->hasPermission('admissions.applications.delete');
    }

    public function enroll(User $actor, AdmissionApplication $application): bool
    {
        return $actor->hasPermission('admissions.enroll.execute');
    }
}
