<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Modules\Students\Models\Student;

/**
 * Hostel → Communication integration. Hostel NEVER sends notifications itself;
 * each hook only publishes a communication request through the engine.
 */
class HostelHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function allocation(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'hostel.allocation', $student, 'Hostel allocated', $detail);
    }

    public function roomTransfer(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'hostel.room_transfer', $student, 'Hostel room transfer', $detail);
    }

    public function feeDue(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'hostel.fee_due', $student, 'Hostel fee due', $detail);
    }

    public function maintenanceCompleted(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'hostel.maintenance_completed', $student, 'Maintenance completed', $detail);
    }

    public function visitorApproved(int $schoolId, Student $student, string $detail): void
    {
        $this->notify($schoolId, 'hostel.visitor_approved', $student, 'Visitor approved', $detail);
    }

    private function notify(int $schoolId, string $event, Student $student, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Custom,
            subject: $subject,
            body: $body,
            source: 'hostel',
            event: $event,
            recipients: [[
                'recipient_type' => Student::class,
                'recipient_id' => $student->id,
                'recipient_name' => (string) $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'user_id' => $student->user_id,
            ]],
        ));
    }
}
