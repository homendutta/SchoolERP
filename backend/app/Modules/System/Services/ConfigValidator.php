<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Environment / configuration validator. Detects missing or unsafe configuration
 * before go-live and powers the production checklist + `system:doctor`.
 */
class ConfigValidator
{
    /**
     * @return array{ready:bool, checks:array<int, array{check:string, ok:bool, severity:string, detail:string}>}
     */
    public function validate(): array
    {
        $checks = [
            $this->check('app_key', (string) config('app.key') !== '', 'critical', 'APP_KEY must be set.'),
            $this->check('app_debug_off', config('app.env') !== 'production' || ! config('app.debug'), 'critical', 'APP_DEBUG must be false in production.'),
            $this->check('app_url', (string) config('app.url') !== 'http://localhost', 'warning', 'Set APP_URL to your real domain.'),
            $this->check('database', $this->dbOk(), 'critical', 'Database must be reachable.'),
            $this->check('storage_writable', $this->storageOk(), 'critical', 'Default storage disk must be writable.'),
            $this->check('queue_not_sync', config('queue.default') !== 'sync', 'warning', 'Use a real queue driver (redis/database) + worker in production.'),
            $this->check('mail_configured', ! in_array((string) config('mail.default'), ['', 'log'], true), 'warning', 'Configure a real mail transport.'),
            $this->check('session_secure', config('app.env') !== 'production' || config('session.secure'), 'warning', 'Enable secure session cookies over HTTPS.'),
        ];

        $ready = ! collect($checks)->contains(fn ($c) => $c['severity'] === 'critical' && ! $c['ok']);

        return ['ready' => $ready, 'checks' => $checks];
    }

    private function dbOk(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function storageOk(): bool
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));
            $disk->put('system/.cfgcheck', 'ok');
            $ok = $disk->exists('system/.cfgcheck');
            $disk->delete('system/.cfgcheck');

            return $ok;
        } catch (Throwable) {
            return false;
        }
    }

    /** @return array{check:string, ok:bool, severity:string, detail:string} */
    private function check(string $name, bool $ok, string $severity, string $detail): array
    {
        return ['check' => $name, 'ok' => $ok, 'severity' => $severity, 'detail' => $ok ? 'OK' : $detail];
    }
}
