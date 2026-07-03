<?php

declare(strict_types=1);

namespace App\Modules\Cms\Providers;

use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Policies\CmsPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * CMS & Public Portal module (Sprint 17).
 *
 * Turns the existing static website into ERP-integrated, CMS-driven content:
 * website settings, homepage sections, pages, notice board, news, events, photo &
 * video galleries, downloads, menus, dynamic forms, contact submissions and
 * admission enquiries. The public site consumes a READ-ONLY published-content API
 * (/cms/public/*); admin management is RBAC-protected (/cms/*). Images use the
 * Media Platform, staff directory reuses the Staff module, admission enquiries are
 * captured only (Admissions is never auto-written), contact forms flow through the
 * Communication Engine, every change is audited and publishing writes Timeline
 * events.
 *
 * Designed so parent/student/teacher/alumni portals, multi-language, a headless
 * CMS, AI content generation, website analytics, live chat, an online-admission
 * portal and a PWA can be added without structural change.
 */
class CmsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Cms';

    protected function registerPolicies(): void
    {
        Gate::policy(Page::class, CmsPolicy::class);
    }
}
