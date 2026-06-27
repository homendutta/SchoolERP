<?php

declare(strict_types=1);

namespace App\Modules\Accounts\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Accounts module.
 *
 * Owns the non-fee day-book of income and expenses and the financial ledger
 * concerns distinct from fee collection. Works alongside Finance; depends on
 * Administration (configuration) and Platform (audit).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class AccountsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Accounts';
}
