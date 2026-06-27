<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Authentication module.
 *
 * Owns login (unified staff/student/parent + super admin), session and password
 * lifecycle, automatic account provisioning, and authentication audit. Depends
 * on Foundation (accounts, numbering, audit, notifications).
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class AuthenticationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Authentication';
}
