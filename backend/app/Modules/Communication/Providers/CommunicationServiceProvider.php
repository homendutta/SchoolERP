<?php

declare(strict_types=1);

namespace App\Modules\Communication\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Communication module.
 *
 * Owns notices, messages (SMS/Email/Push) and templates, communication logs,
 * the photo/video galleries and one-way website/app sync, and the support
 * channels (complaints, helpdesk). Uses the central notification service.
 * Depends on Foundation; references Students, Staff, and Academic for audiences;
 * consumes domain events from other modules as triggers.
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class CommunicationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Communication';
}
