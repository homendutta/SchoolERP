<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\BusinessRuleException;

/**
 * Resolves a borrower through the Identity Platform. Borrowing NEVER uses a raw
 * Student ID or Staff ID — the Identity Number is the key, and the owner must be
 * a Student or Staff member.
 */
class BorrowerResolver
{
    public function __construct(private readonly IdentityService $identities) {}

    public function resolve(int $schoolId, string $identityNumber): Identity
    {
        $identity = $this->identities->lookup($identityNumber);

        if ($identity === null || (int) $identity->school_id !== $schoolId) {
            throw BusinessRuleException::make('No borrower found for this identity number.', 'BORROWER_NOT_FOUND');
        }

        $owner = $identity->owner;
        if (! $owner instanceof Student && ! $owner instanceof Staff) {
            throw BusinessRuleException::make('Only students and staff can borrow.', 'INVALID_BORROWER');
        }

        return $identity;
    }
}
