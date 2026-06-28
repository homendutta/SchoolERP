<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Resolve an Identity from its public identifier or identity number (used by
 * verification / scanning flows). Returns null when not found.
 */
class IdentityLookupAction implements Action
{
    use AsAction;

    public function __construct(private readonly IdentityService $service) {}

    public function handle(string $identifier): ?Identity
    {
        return $this->service->lookup($identifier);
    }
}
