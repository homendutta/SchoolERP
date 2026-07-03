<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/**
 * HR → Communication integration. HR NEVER sends notifications itself; each hook
 * publishes a communication request through the engine, which resolves
 * recipients, templates, preferences and delivery.
 */
class HrHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function leaveApproved(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'hr.leave_approved', 'Leave approved', $detail);
    }

    public function leaveRejected(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'hr.leave_rejected', 'Leave rejected', $detail);
    }

    public function reviewScheduled(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'hr.review_scheduled', 'Performance review scheduled', $detail);
    }

    public function trainingAssigned(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'hr.training_assigned', 'Training assigned', $detail);
    }

    public function separationInitiated(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'hr.separation_initiated', 'Separation initiated', $detail);
    }

    private function notify(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'hr',
            event: $event,
        ));
    }
}
