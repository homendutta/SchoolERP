<?php

declare(strict_types=1);

namespace App\Modules\Students\Support;

/**
 * Well-known student timeline event types owned by the Students module. The
 * `event_type` column is a free string by design so that every future module
 * (Fees, Attendance, …) can write its own events without changing this module.
 */
final class TimelineEvent
{
    public const Created = 'student.created';

    public const ProfileUpdated = 'student.profile_updated';

    public const PhotoChanged = 'student.photo_changed';

    public const MedicalUpdated = 'student.medical_updated';

    public const DocumentAdded = 'student.document_added';

    public const Promoted = 'student.promoted';

    public const Transferred = 'student.transferred';

    public const Withdrawn = 'student.withdrawn';

    public const Graduated = 'student.graduated';
}
