<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/**
 * LMS → Communication integration. The LMS NEVER sends notifications directly;
 * each hook publishes a request through the Communication Engine.
 */
class LmsHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function publish(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'lms',
            event: $event,
        ));
    }
}
