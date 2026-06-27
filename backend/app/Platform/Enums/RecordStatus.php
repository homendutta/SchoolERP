<?php

declare(strict_types=1);

namespace App\Platform\Enums;

/** Generic active/archived status used by soft-deletable records. */
enum RecordStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
