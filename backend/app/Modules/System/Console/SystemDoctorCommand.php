<?php

declare(strict_types=1);

namespace App\Modules\System\Console;

use App\Modules\System\Services\ConfigValidator;
use App\Modules\System\Services\HealthService;
use Illuminate\Console\Command;

/**
 * `php artisan system:doctor` — the production readiness check. Runs the config
 * validator + health checks and exits non-zero when a critical check fails
 * (usable in a deployment pipeline / post-install verification).
 */
class SystemDoctorCommand extends Command
{
    protected $signature = 'system:doctor';

    protected $description = 'Run production readiness diagnostics (config validation + health checks).';

    public function handle(ConfigValidator $config, HealthService $health): int
    {
        $this->info('Asylinx ERP — System Doctor');

        $validation = $config->validate();
        $this->line('');
        $this->line('Configuration:');
        $this->table(['Check', 'OK', 'Severity', 'Detail'], array_map(fn ($c) => [
            $c['check'], $c['ok'] ? 'yes' : 'NO', $c['severity'], $c['detail'],
        ], $validation['checks']));

        $h = $health->check();
        $this->line('');
        $this->line("Health score: {$h['score']}/100 ({$h['status']})");
        $this->table(['Component', 'Status', 'Detail'], array_map(fn ($c) => [
            $c['name'], $c['status'], $c['detail'],
        ], $h['components']));

        if (! $validation['ready']) {
            $this->error('Not production ready — resolve the critical checks above.');

            return self::FAILURE;
        }

        $this->info('All critical checks passed.');

        return self::SUCCESS;
    }
}
