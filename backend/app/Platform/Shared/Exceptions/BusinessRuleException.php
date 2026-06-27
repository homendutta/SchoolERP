<?php

declare(strict_types=1);

namespace App\Platform\Shared\Exceptions;

/** A violated business rule (e.g., sequence exhausted, gateway misconfigured). */
class BusinessRuleException extends DomainException
{
    public static function make(string $message, ?string $code = null): self
    {
        return new self($message, 422, $code);
    }
}
