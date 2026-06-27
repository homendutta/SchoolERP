<?php

declare(strict_types=1);

namespace App\Platform\Shared\DTO;

/**
 * Lightweight base for immutable Data Transfer Objects. Concrete DTOs are
 * `readonly` classes built from validated input (e.g., a Form Request) and
 * passed into Services/Actions instead of loose arrays.
 */
abstract class DataTransferObject
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Only the non-null public properties — handy for partial updates.
     *
     * @return array<string, mixed>
     */
    public function filled(): array
    {
        return array_filter($this->toArray(), static fn ($v) => $v !== null);
    }
}
