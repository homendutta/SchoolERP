<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\Enums\IdentityStatus;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Update the only mutable aspect of an Identity — its status. The immutable
 * fields (identity_number, public_identifier, owner) can never be changed.
 */
class UpdateIdentityAction implements Action
{
    use AsAction;

    public function __construct(private readonly IdentityService $service) {}

    public function handle(Identity $identity, IdentityStatus $status): Identity
    {
        return $this->service->setStatus($identity, $status);
    }
}
