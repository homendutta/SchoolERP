<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

/** Outcome of a logged integration request. */
enum LogStatus: string
{
    case Success = 'success';
    case Failure = 'failure';

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
