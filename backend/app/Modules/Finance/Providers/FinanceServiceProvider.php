<?php

declare(strict_types=1);

namespace App\Modules\Finance\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Finance module.
 *
 * Owns the money lifecycle: fee structures, monthly dues, payments/receipts,
 * refunds, online gateway transactions, and the non-fee day-book. Depends on
 * Students, Academic, and Foundation; references Admission and Staff.
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class FinanceServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Finance';
}
