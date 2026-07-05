<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use App\Modules\Integrations\Models\Provider;
use App\Modules\System\Enums\HealthState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Health checks for every critical dependency (database, cache, queue, storage,
 * scheduler, mail, integrations) plus an overall weighted health score. Read-only.
 */
class HealthService
{
    /**
     * @return array{score:int, status:string, components:array<int, array{name:string, status:string, detail:string}>}
     */
    public function check(): array
    {
        $components = [
            $this->database(),
            $this->cache(),
            $this->queue(),
            $this->storage(),
            $this->scheduler(),
            $this->mail(),
            $this->integrations(),
        ];

        $score = (int) round(array_sum(array_map(
            fn ($c) => HealthState::from($c['status'])->weight(), $components
        )) / max(1, count($components)));

        $status = $score >= 90 ? 'ok' : ($score >= 60 ? 'warn' : 'down');

        return ['score' => $score, 'status' => $status, 'components' => $components];
    }

    /** @return array{name:string, status:string, detail:string} */
    private function database(): array
    {
        try {
            DB::select('select 1');

            return $this->c('database', HealthState::Ok, 'Connection OK ('.config('database.default').')');
        } catch (Throwable $e) {
            return $this->c('database', HealthState::Down, $e->getMessage());
        }
    }

    private function cache(): array
    {
        try {
            Cache::put('system.health.ping', '1', 5);

            return Cache::get('system.health.ping') === '1'
                ? $this->c('cache', HealthState::Ok, 'Driver: '.config('cache.default'))
                : $this->c('cache', HealthState::Warn, 'Write/read mismatch.');
        } catch (Throwable $e) {
            return $this->c('cache', HealthState::Down, $e->getMessage());
        }
    }

    private function queue(): array
    {
        $connection = (string) config('queue.default');
        $state = $connection === 'sync' ? HealthState::Warn : HealthState::Ok;
        $detail = $connection === 'sync' ? 'Sync driver — configure a worker for production.' : "Driver: {$connection}";

        return $this->c('queue', $state, $detail);
    }

    private function storage(): array
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));
            $disk->put('system/.health', 'ok');
            $ok = $disk->exists('system/.health');
            $disk->delete('system/.health');

            return $ok ? $this->c('storage', HealthState::Ok, 'Writable ('.config('filesystems.default').')')
                : $this->c('storage', HealthState::Warn, 'Not writable.');
        } catch (Throwable $e) {
            return $this->c('storage', HealthState::Down, $e->getMessage());
        }
    }

    private function scheduler(): array
    {
        $last = Cache::get('system.scheduler.last_run');

        return $last !== null
            ? $this->c('scheduler', HealthState::Ok, 'Last run: '.$last)
            : $this->c('scheduler', HealthState::Warn, 'No heartbeat yet — ensure the cron entry is installed.');
    }

    private function mail(): array
    {
        $mailer = (string) config('mail.default');

        return $mailer === '' || $mailer === 'log'
            ? $this->c('mail', HealthState::Warn, "Mailer '{$mailer}' — configure a real transport for production.")
            : $this->c('mail', HealthState::Ok, "Mailer: {$mailer}");
    }

    private function integrations(): array
    {
        if (! Schema::hasTable('integration_providers')) {
            return $this->c('integrations', HealthState::Ok, 'No providers configured.');
        }
        $down = Provider::query()->where('status', 'enabled')->where('health', 'down')->count();

        return $down === 0
            ? $this->c('integrations', HealthState::Ok, 'All enabled providers healthy.')
            : $this->c('integrations', HealthState::Warn, "{$down} provider(s) reporting down.");
    }

    /** @return array{name:string, status:string, detail:string} */
    private function c(string $name, HealthState $state, string $detail): array
    {
        return ['name' => $name, 'status' => $state->value, 'detail' => $detail];
    }
}
