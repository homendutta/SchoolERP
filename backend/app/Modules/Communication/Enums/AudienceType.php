<?php

declare(strict_types=1);

namespace App\Modules\Communication\Enums;

/**
 * Recipient groups. Resolution of an audience into concrete recipients belongs
 * to the Communication Engine (RecipientResolver), never to callers.
 */
enum AudienceType: string
{
    case School = 'school';
    case ClassGroup = 'class';
    case Section = 'section';
    case Students = 'students';
    case Guardians = 'guardians';
    case Staff = 'staff';
    case Teachers = 'teachers';
    case Administrators = 'administrators';
    case Department = 'department';
    case Custom = 'custom';

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
