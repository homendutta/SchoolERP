<?php

declare(strict_types=1);

namespace App\Modules\Website\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Website module.
 *
 * The dedicated module responsible for the public web surface and its sync:
 *   - Public Website Integration (one domain; existing site retained, no CMS)
 *   - Notice Publishing (outward)
 *   - Photo Gallery & Video Gallery
 *   - Public APIs (read-only outward feed)
 *
 * One-way ERP -> website/app. Consumes notices/gallery from Communication;
 * depends on Platform (media) and Administration (configuration).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class WebsiteServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Website';
}
