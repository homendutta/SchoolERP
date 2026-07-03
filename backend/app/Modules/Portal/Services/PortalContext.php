<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Portal\Enums\PortalRole;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Collection;

/**
 * Immutable resolved portal context for the current request: the portal role and
 * the linked person(s) the authenticated user represents.
 */
final class PortalContext
{
    /**
     * @param  Collection<int, Student>  $students
     */
    public function __construct(
        public readonly PortalRole $role,
        public readonly User $user,
        public readonly Collection $students,
        public readonly ?Guardian $guardian,
        public readonly ?Staff $staff,
    ) {}
}
