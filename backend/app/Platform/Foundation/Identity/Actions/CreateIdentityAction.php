<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Database\Eloquent\Model;

/**
 * Issue (or return the existing) Identity for an owner. The single entry point
 * modules use during their creation workflows.
 */
class CreateIdentityAction implements Action
{
    use AsAction;

    public function __construct(private readonly IdentityService $service) {}

    public function handle(Model $owner, IdentityType $type, ?string $manualNumber = null): Identity
    {
        return $this->service->ensureFor($owner, $type, $manualNumber);
    }
}
