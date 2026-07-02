<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/**
 * The formal ASSET LIFECYCLE state. Every physical asset moves through these
 * states; every transition writes to the Audit Log and the asset Timeline.
 * Assets are never physically deleted and disposed assets remain searchable.
 *
 * `Ordered` is reserved for future Procurement integration (not implemented).
 */
enum AssetStatus: string
{
    case Draft = 'draft';
    case Ordered = 'ordered';
    case Received = 'received';
    case Available = 'available';
    case Assigned = 'assigned';
    case Reserved = 'reserved';
    case InMaintenance = 'in_maintenance';
    case Lost = 'lost';
    case Stolen = 'stolen';
    case Disposed = 'disposed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Can the asset be assigned from this state? */
    public function isAssignable(): bool
    {
        return in_array($this, [self::Available, self::Received], true);
    }
}
