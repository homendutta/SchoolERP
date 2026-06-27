<?php

declare(strict_types=1);

namespace App\Modules\Academic\DTO;

use App\Platform\Shared\DTO\DataTransferObject;

final class SubjectData extends DataTransferObject
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?int $subject_type_id = null,
        public readonly ?string $short_name = null,
        public readonly bool $theory = true,
        public readonly bool $practical = false,
        public readonly int $credits = 0,
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
            subject_type_id: $d['subject_type_id'] ?? null,
            short_name: $d['short_name'] ?? null,
            theory: (bool) ($d['theory'] ?? true),
            practical: (bool) ($d['practical'] ?? false),
            credits: (int) ($d['credits'] ?? 0),
            school_id: $d['school_id'] ?? null,
            display_order: (int) ($d['display_order'] ?? 0),
        );
    }
}
