<?php

declare(strict_types=1);

namespace App\Platform\Enums;

/** Test vs. live operating mode, shared by all gateway types. */
enum GatewayMode: string
{
    case Test = 'test';
    case Live = 'live';
}
