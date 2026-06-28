<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Render the QR image (SVG) dynamically — image files are never persisted.
 */
class GenerateQrImageAction implements Action
{
    use AsAction;

    public function __construct(private readonly IdentityService $service) {}

    public function handle(Identity $identity, int $size = 220): string
    {
        return $this->service->qrSvg($identity, $size);
    }
}
