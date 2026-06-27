<?php

declare(strict_types=1);

namespace App\Modules\Administration\Enums;

enum GatewayType: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Payment = 'payment';
}
