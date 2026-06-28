<?php

declare(strict_types=1);

namespace App\Modules\Examination\Enums;

/** Pass/Fail outcome of a processed result. */
enum ResultStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Pending = 'pending';

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
