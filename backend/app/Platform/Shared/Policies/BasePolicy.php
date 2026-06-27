<?php

declare(strict_types=1);

namespace App\Platform\Shared\Policies;

/**
 * Base policy for every module.
 *
 * Policies express the RBAC action grant (View/Create/Edit/Delete/Print/Export/
 * Import/Approve/Publish/Lock/Unlock and the extended actions) for a module's
 * resources. Data scope (own/linked/assigned/all) is enforced together with the
 * grant. Enforcement is always server-side; client-side gating is advisory.
 *
 * Concrete module policies extend this and implement per-ability methods.
 * No business rules are defined at the foundation stage.
 */
abstract class BasePolicy {}
