<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationBatch;

/**
 * Reusable integration surface for business modules. Modules call these hooks
 * instead of sending anything themselves — each hook ONLY publishes a
 * communication request through the engine (no business rules embedded here).
 *
 * Listeners for domain events (Admissions, Student Created, Attendance Marked,
 * Fee Due/Paid, Exam/Result Published, Promotion, Staff Joined/Resigned …) are
 * prepared as thin wrappers over dispatch(); wiring a module event to a hook is
 * a one-line listener, with zero structural change to this module.
 */
class CommunicationHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    /**
     * Generic publish hook. Resolves a template by code for the given channel and
     * fans out to the audience.
     *
     * @param  array<string, scalar|null>  $variables
     * @param  array{class_id?:int|null, section_id?:int|null, department_id?:int|null, mandatory?:bool, scheduled_at?:string|null, channel?:CommunicationChannel}  $options
     */
    public function dispatch(
        string $event,
        int $schoolId,
        string $templateCode,
        AudienceType $audience,
        array $variables = [],
        array $options = [],
    ): CommunicationBatch {
        return $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: $options['channel'] ?? CommunicationChannel::InApp,
            audienceType: $audience,
            classId: $options['class_id'] ?? null,
            sectionId: $options['section_id'] ?? null,
            departmentId: $options['department_id'] ?? null,
            templateCode: $templateCode,
            variables: $variables,
            isMandatory: $options['mandatory'] ?? false,
            scheduledAt: $options['scheduled_at'] ?? null,
            source: explode('.', $event)[0],
            event: $event,
        ));
    }

    /** @param array<string, scalar|null> $vars */
    public function studentCreated(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('students.student_created', $schoolId, 'student_created', AudienceType::Guardians, $vars, $options);
    }

    /** @param array<string, scalar|null> $vars */
    public function attendanceMarked(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('attendance.attendance_marked', $schoolId, 'attendance_marked', AudienceType::Guardians, $vars, $options);
    }

    /** @param array<string, scalar|null> $vars */
    public function feeDue(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('finance.fee_due', $schoolId, 'fee_due', AudienceType::Guardians, $vars, $options + ['mandatory' => true]);
    }

    /** @param array<string, scalar|null> $vars */
    public function feePaid(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('finance.fee_paid', $schoolId, 'fee_paid', AudienceType::Guardians, $vars, $options);
    }

    /** @param array<string, scalar|null> $vars */
    public function resultPublished(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('examination.result_published', $schoolId, 'result_published', AudienceType::Guardians, $vars, $options);
    }

    /** @param array<string, scalar|null> $vars */
    public function staffJoined(int $schoolId, array $vars, array $options = []): CommunicationBatch
    {
        return $this->dispatch('staff.staff_joined', $schoolId, 'staff_joined', AudienceType::Staff, $vars, $options);
    }
}
