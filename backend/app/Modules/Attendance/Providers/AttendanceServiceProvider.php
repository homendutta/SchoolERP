<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Providers;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Policies\AttendancePolicy;
use App\Modules\Attendance\Support\AttendanceImporter;
use App\Modules\Attendance\Support\Biometric\ConnectorRegistry;
use App\Modules\Attendance\Support\Biometric\EsslMb20Connector;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Attendance module.
 *
 * Owns the reusable Attendance Engine that serves manual, import and biometric
 * sources identically. People are matched via the Platform Identity Service, so
 * one engine serves students and staff. Biometric devices are vendor-independent
 * through the connector layer (eSSL MB20 ships by default).
 */
class AttendanceServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Attendance';

    protected function registerBindings(): void
    {
        // Vendor-independent biometric connectors. New vendors register here —
        // the Attendance Engine never changes.
        $this->app->singleton(ConnectorRegistry::class, function (): ConnectorRegistry {
            $registry = new ConnectorRegistry;
            $registry->register(new EsslMb20Connector);

            return $registry;
        });

        // Attendance import plugs into the generic Import framework.
        $this->callAfterResolving(ImporterRegistry::class, function (ImporterRegistry $registry): void {
            $registry->register($this->app->make(AttendanceImporter::class));
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(AttendanceRecord::class, AttendancePolicy::class);
    }
}
