<?php

declare(strict_types=1);

namespace App\Modules\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `php artisan system:cleanup` — the daily housekeeping task (registered in the
 * central scheduler). Records the scheduler heartbeat and prunes old, high-volume
 * operational logs. Never touches business data.
 */
class SystemCleanupCommand extends Command
{
    protected $signature = 'system:cleanup {--days=90 : Retain operational logs for this many days}';

    protected $description = 'Record the scheduler heartbeat and prune old operational logs.';

    public function handle(): int
    {
        Cache::forever('system.scheduler.last_run', now()->toDateTimeString());

        $cutoff = now()->subDays((int) $this->option('days'))->toDateTimeString();
        $pruned = 0;

        foreach (['integration_logs', 'integration_webhook_deliveries'] as $table) {
            if (Schema::hasTable($table)) {
                $pruned += DB::table($table)->where('created_at', '<', $cutoff)->delete();
            }
        }

        $this->info("Scheduler heartbeat recorded. Pruned {$pruned} old operational row(s).");

        return self::SUCCESS;
    }
}
