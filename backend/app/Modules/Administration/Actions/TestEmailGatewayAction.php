<?php

declare(strict_types=1);

namespace App\Modules\Administration\Actions;

use App\Modules\Administration\Models\EmailGateway;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Validates an email gateway's configuration (connection-level check). A real
 * SMTP send is wired by the Communication module; here we verify completeness so
 * the Administration UI can report readiness.
 */
class TestEmailGatewayAction implements Action
{
    use AsAction;

    /** @return array{ok: bool, message: string} */
    public function handle(EmailGateway $gateway, ?string $recipient = null): array
    {
        $missing = [];
        foreach (['host', 'port', 'from_address'] as $field) {
            if (blank($gateway->{$field})) {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            return ['ok' => false, 'message' => 'Missing configuration: '.implode(', ', $missing)];
        }

        return [
            'ok' => true,
            'message' => 'SMTP configuration is valid'
                .($recipient ? " — a test message can be sent to {$recipient}." : '.'),
        ];
    }
}
