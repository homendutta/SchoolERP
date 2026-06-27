<?php

declare(strict_types=1);

namespace App\Modules\Academic\DTO;

use App\Platform\Shared\DTO\DataTransferObject;

final class AcademicYearData extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly ?string $short_name = null,
        public readonly ?int $school_id = null,
        public readonly int $sort_order = 0,
        public readonly ?int $version = null,
    ) {}

    /** @param array<string, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            name: (string) $d['name'],
            slug: (string) $d['slug'],
            start_date: (string) $d['start_date'],
            end_date: (string) $d['end_date'],
            short_name: $d['short_name'] ?? null,
            school_id: $d['school_id'] ?? null,
            sort_order: (int) ($d['sort_order'] ?? 0),
            version: isset($d['version']) ? (int) $d['version'] : null,
        );
    }
}
