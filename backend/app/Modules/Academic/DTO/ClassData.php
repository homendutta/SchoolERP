<?php

declare(strict_types=1);

namespace App\Modules\Academic\DTO;

use App\Platform\Shared\DTO\DataTransferObject;

final class ClassData extends DataTransferObject
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $short_name = null,
        public readonly ?int $school_id = null,
        public readonly int $display_order = 0,
    ) {}

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            code: (string) $d['code'],
            name: (string) $d['name'],
            slug: (string) $d['slug'],
            short_name: $d['short_name'] ?? null,
            school_id: $d['school_id'] ?? null,
            display_order: (int) ($d['display_order'] ?? 0),
        );
    }
}
