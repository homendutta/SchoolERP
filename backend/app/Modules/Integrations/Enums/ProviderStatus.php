<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

/** Whether a provider is available for selection. */
enum ProviderStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';

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
