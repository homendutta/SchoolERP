<?php

declare(strict_types=1);

namespace App\Platform\Shared\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Base queued job for every module.
 *
 * Heavy, bulk, and scheduled work runs asynchronously (notification dispatch,
 * monthly dues generation, bulk import/export, report generation, payment
 * reconciliation). Jobs carry actor/context so audit and data scope stay
 * correct off-request, and are idempotent where they cause external effects.
 *
 * Concrete module jobs implement handle(); no business logic here.
 */
abstract class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Default retry attempts for jobs with external effects (overridable).
     */
    public int $tries = 3;

    /**
     * Default backoff (seconds) between retries.
     */
    public int $backoff = 10;
}
