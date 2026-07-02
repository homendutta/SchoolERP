<?php

declare(strict_types=1);

namespace App\Modules\Library\Providers;

use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Policies\LibraryPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Library module.
 *
 * Manages the catalog, physical copies (each with its own permanent platform
 * Identity for barcode/QR), circulation (borrow/return/renew/reserve with the
 * borrower resolved through the Identity Platform), reservations, fines
 * (calculated here; collected by Finance), inventory verification and reporting.
 * Reminders are published through the Communication Engine — the Library never
 * sends messages itself.
 */
class LibraryServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Library';

    protected function registerPolicies(): void
    {
        Gate::policy(Borrowing::class, LibraryPolicy::class);
    }
}
