<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Enums;

/**
 * The kind of person an Identity represents. New owner types (Visitor, Alumni,
 * Vendor, …) are added here without touching the rest of the platform — modules
 * never hardcode owner names.
 */
enum IdentityType: string
{
    case Student = 'student';
    case Guardian = 'guardian';
    case Staff = 'staff';

    // Designed-for (no business module yet).
    case Visitor = 'visitor';
    case Alumni = 'alumni';
    case Vendor = 'vendor';
    case TransportDriver = 'transport_driver';
    case LibraryMember = 'library_member';
    case LibraryCopy = 'library_copy';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
