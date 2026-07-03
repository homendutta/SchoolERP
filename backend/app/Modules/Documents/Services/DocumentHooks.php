<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/** Documents → Communication integration (never sends notifications directly). */
class DocumentHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function certificateIssued(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'documents.certificate_issued', 'Certificate issued', $detail);
    }

    public function documentReady(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'documents.document_ready', 'Document ready', $detail);
    }

    public function verificationCompleted(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'documents.verification_completed', 'Verification completed', $detail);
    }

    private function notify(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'documents',
            event: $event,
        ));
    }
}
