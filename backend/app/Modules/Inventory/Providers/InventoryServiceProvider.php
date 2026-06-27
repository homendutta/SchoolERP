<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Inventory module.
 *
 * Owns consumable stock items, stock transactions (in/out/adjustment) with
 * current-stock consistency, and reorder alerts. References Staff (performer/
 * approver) and Platform (numbering, audit).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class InventoryServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Inventory';
}
