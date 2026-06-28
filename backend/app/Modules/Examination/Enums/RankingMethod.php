<?php

declare(strict_types=1);

namespace App\Modules\Examination\Enums;

/**
 * Configurable ranking strategy. Schools choose how ranks are computed (or
 * disable ranking entirely).
 */
enum RankingMethod: string
{
    case Dense = 'dense';            // 1,1,2,3 …
    case Competition = 'competition'; // 1,1,3,4 …
    case None = 'none';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
