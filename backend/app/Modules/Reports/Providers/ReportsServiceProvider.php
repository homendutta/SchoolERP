<?php

declare(strict_types=1);

namespace App\Modules\Reports\Providers;

use App\Modules\Reports\Support\ReportRegistry;
use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Reports & Printing Center (Sprint 21).
 *
 * The single, reusable reporting + printing platform for the whole ERP. It owns no
 * business data — it CONSUMES every module (Academic, Attendance, Examination,
 * Finance, HR/Payroll, Library/Transport/Hostel/Inventory/LMS/Documents, Audit)
 * through a code-registered catalog of report definitions, then runs them through
 * one Reporting Engine (filters/sort/group/paginate/totals), one Export Engine
 * (CSV + Excel; pluggable drivers) and one Print/PDF Engine (A4/A5/orientation +
 * logo/header/footer/watermark/signature). Large + scheduled exports use queues;
 * scheduled delivery uses the Communication Engine; every export is audited.
 *
 * The ReportRegistry is a singleton so any module can register additional report
 * definitions at boot without touching the engine, export or print layers —
 * keeping the door open for Power BI / Tableau / Looker / AI insights / a custom
 * SQL builder / user-designed reports with no structural change.
 */
class ReportsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Reports';

    protected function registerBindings(): void
    {
        $this->app->singleton(ReportRegistry::class);
    }
}
