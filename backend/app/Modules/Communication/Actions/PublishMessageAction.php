<?php

declare(strict_types=1);

namespace App\Modules\Communication\Actions;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationBatch;
use App\Modules\Communication\Services\CommunicationEngine;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\Auth;

/**
 * Publish a communication request through the engine. This is the ONLY way to
 * send a message — there is no direct-send path for any module.
 */
class PublishMessageAction implements Action
{
    use AsAction;

    public function __construct(private readonly CommunicationEngine $engine) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): CommunicationBatch
    {
        return $this->engine->publish(new CommunicationRequestData(
            schoolId: (int) $payload['school_id'],
            channel: CommunicationChannel::from((string) $payload['channel']),
            audienceType: AudienceType::from((string) $payload['audience_type']),
            classId: isset($payload['class_id']) ? (int) $payload['class_id'] : null,
            sectionId: isset($payload['section_id']) ? (int) $payload['section_id'] : null,
            departmentId: isset($payload['department_id']) ? (int) $payload['department_id'] : null,
            templateCode: $payload['template_code'] ?? null,
            subject: $payload['subject'] ?? null,
            body: $payload['body'] ?? null,
            variables: (array) ($payload['variables'] ?? []),
            isMandatory: (bool) ($payload['is_mandatory'] ?? false),
            scheduledAt: $payload['scheduled_at'] ?? null,
            source: $payload['source'] ?? 'manual',
            event: $payload['event'] ?? null,
            createdBy: Auth::id(),
            recipients: (array) ($payload['recipients'] ?? []),
        ));
    }
}
