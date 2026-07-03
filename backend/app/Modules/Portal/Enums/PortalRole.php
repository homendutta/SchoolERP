<?php

declare(strict_types=1);

namespace App\Modules\Portal\Enums;

/** Which self-service portal the authenticated user belongs to. */
enum PortalRole: string
{
    case Parent = 'parent';
    case Student = 'student';
    case Teacher = 'teacher';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
