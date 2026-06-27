<?php

declare(strict_types=1);

namespace App\Platform\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base domain event for every module.
 *
 * Modules stay decoupled by emitting domain events (e.g., StudentEnrolled,
 * FeeCollected, ExamPublished) that cross-cutting listeners react to — writing
 * audit entries, enqueuing notifications, updating the search index, and
 * invalidating caches. Concrete events carry their immutable payload.
 */
abstract class DomainEvent
{
    use Dispatchable;
    use SerializesModels;
}
