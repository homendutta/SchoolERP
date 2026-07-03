<?php

declare(strict_types=1);

namespace App\Modules\Portal\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Parent / Student / Teacher Portal module (Sprint 18).
 *
 * Secure self-service portals that CONSUME the ERP and own no business logic.
 * Each request resolves to a portal role (parent/student/teacher) and is isolated
 * to the caller's data: parents see only linked children, students only their own
 * records, teachers only their responsibilities. Attendance/Examination/Finance/
 * Library/Transport/Hostel/Communication/Timetable/CMS are all read through their
 * owning modules; online fee payment reuses the Finance Payment Engine + Gateway
 * abstraction (parents may pay for multiple children in one atomic transaction;
 * students pay only their own). Finance stays the source of truth; Communication
 * sends confirmations; Timeline records payments; Audit records profile changes.
 *
 * Designed so Homework, Assignments, an LMS, Online Classes, Parent-Teacher Chat,
 * AI assistants, push enhancements, a PWA, Digital Student ID and Digital Report
 * Cards can be added without structural change.
 */
class PortalServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Portal';
}
