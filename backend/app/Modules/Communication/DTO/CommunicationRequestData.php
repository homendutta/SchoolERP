<?php

declare(strict_types=1);

namespace App\Modules\Communication\DTO;

use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Platform\Shared\DTO\DataTransferObject;

/**
 * A communication request published by a business module (or the manual UI).
 * The engine resolves the template, recipients, preferences, queueing and
 * delivery — callers never touch a transport.
 */
final class CommunicationRequestData extends DataTransferObject
{
    /**
     * @param  array<string, scalar|null>  $variables
     * @param  array<int, array<string, mixed>>  $recipients  custom recipients (Custom audience)
     */
    public function __construct(
        public readonly int $schoolId,
        public readonly CommunicationChannel $channel,
        public readonly AudienceType $audienceType,
        public readonly ?int $classId = null,
        public readonly ?int $sectionId = null,
        public readonly ?int $departmentId = null,
        public readonly ?string $templateCode = null,
        public readonly ?string $subject = null,
        public readonly ?string $body = null,
        public readonly array $variables = [],
        public readonly bool $isMandatory = false,
        public readonly ?string $scheduledAt = null,
        public readonly ?string $source = null,
        public readonly ?string $event = null,
        public readonly ?int $createdBy = null,
        public readonly array $recipients = [],
    ) {}
}
