<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Policies\InventoryPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Inventory & Asset module.
 *
 * Manages asset categories/models, vendors, physical assets (each with its OWN
 * permanent Identity for barcode/QR; asset number from the Number Generator),
 * consumables (append-only stock movements — never mixed with assets, never
 * given an Identity), historical assignments/transfers, maintenance, warranties,
 * physical verification and disposal. Documents use the Media Platform; warranty/
 * low-stock reminders go through the Communication Engine; depreciation is stored
 * as metadata only (Finance calculates later). Procurement / barcode hardware /
 * RFID / IoT are designed-for without any structural change.
 */
class InventoryServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Inventory';

    protected function registerPolicies(): void
    {
        Gate::policy(Asset::class, InventoryPolicy::class);
    }
}
