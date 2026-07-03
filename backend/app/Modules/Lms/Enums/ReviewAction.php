<?php

declare(strict_types=1);

namespace App\Modules\Lms\Enums;

/** A teacher review action on a submission. All reviews are historical. */
enum ReviewAction: string
{
    case Comment = 'comment';
    case Grade = 'grade';
    case Return = 'return';
    case Approve = 'approve';

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
