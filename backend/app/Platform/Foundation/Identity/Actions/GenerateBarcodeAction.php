<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Render the barcode image (SVG) dynamically from the stored barcode value.
 */
class GenerateBarcodeAction implements Action
{
    use AsAction;

    public function __construct(private readonly IdentityService $service) {}

    public function handle(Identity $identity): string
    {
        return $this->service->barcodeSvg($identity);
    }
}
