<?php

declare(strict_types=1);

namespace App\Modules\Staff\Support;

/**
 * Well-known staff timeline event types owned by the Staff module. The
 * `event_type` column is a free string by design so every future module
 * (Payroll, Leave, Attendance, …) can append its own events without changing
 * this module.
 */
final class TimelineEvent
{
    public const Created = 'staff.created';

    public const ProfileUpdated = 'staff.profile_updated';

    public const DepartmentChanged = 'staff.department_changed';

    public const Promoted = 'staff.promoted';

    public const DocumentAdded = 'staff.document_added';

    public const Resigned = 'staff.resigned';

    public const Retired = 'staff.retired';
}
