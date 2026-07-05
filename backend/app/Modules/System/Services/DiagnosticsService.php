<?php

declare(strict_types=1);

namespace App\Modules\System\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/** System diagnostics — versions, drivers and resource usage (read-only). */
class DiagnosticsService
{
    /**
     * @return array<string, mixed>
     */
    public function info(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => (string) config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'database' => $this->database(),
            'cache_driver' => (string) config('cache.default'),
            'queue_driver' => (string) config('queue.default'),
            'storage_driver' => (string) config('filesystems.default'),
            'mail_driver' => (string) config('mail.default'),
            'disk' => $this->disk(),
            'php_extensions' => $this->extensions(),
        ];
    }

    /**
     * @return array{driver:string, version:string}
     */
    private function database(): array
    {
        $driver = (string) config('database.default');
        try {
            $version = (string) DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (Throwable) {
            $version = 'unknown';
        }

        return ['driver' => $driver, 'version' => $version];
    }

    /**
     * @return array{free:int, total:int, used_percent:float}
     */
    private function disk(): array
    {
        $path = base_path();
        $free = @disk_free_space($path) ?: 0;
        $total = @disk_total_space($path) ?: 0;
        $usedPercent = $total > 0 ? round(($total - $free) / $total * 100, 1) : 0.0;

        return ['free' => (int) $free, 'total' => (int) $total, 'used_percent' => $usedPercent];
    }

    /**
     * @return array<string, bool>
     */
    private function extensions(): array
    {
        $required = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'curl', 'fileinfo', 'gd'];

        return array_combine($required, array_map('extension_loaded', $required));
    }
}
