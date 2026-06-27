<?php

declare(strict_types=1);

namespace App\Platform\Shared\Exceptions;

use RuntimeException;

/**
 * Base for domain/business errors. The Shared kernel translates these into the
 * standard API error envelope.
 */
class DomainException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($message);
    }
}
