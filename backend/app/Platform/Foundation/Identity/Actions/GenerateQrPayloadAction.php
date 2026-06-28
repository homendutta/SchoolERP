<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Actions;

use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Return the QR payload (data only — never an image, never internal ids).
 */
class GenerateQrPayloadAction implements Action
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(Identity $identity): array
    {
        return (array) $identity->qr_payload;
    }
}
