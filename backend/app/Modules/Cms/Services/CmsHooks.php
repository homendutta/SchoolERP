<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Services\CommunicationEngine;

/**
 * CMS → Communication integration. The CMS NEVER sends email/notifications
 * directly; each hook publishes a request through the Communication Engine.
 */
class CmsHooks
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    public function contactSubmitted(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'cms.contact_submitted', 'New contact form submission', $detail);
    }

    public function enquirySubmitted(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'cms.enquiry_submitted', 'New admission enquiry', $detail);
    }

    public function eventPublished(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'cms.event_published', 'Event published', $detail);
    }

    public function noticePublished(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'cms.notice_published', 'Notice published', $detail);
    }

    public function newsPublished(int $schoolId, string $detail): void
    {
        $this->notify($schoolId, 'cms.news_published', 'News published', $detail);
    }

    private function notify(int $schoolId, string $event, string $subject, string $body): void
    {
        $this->engine->publish(new CommunicationRequestData(
            schoolId: $schoolId,
            channel: CommunicationChannel::InApp,
            audienceType: AudienceType::Administrators,
            subject: $subject,
            body: $body,
            source: 'cms',
            event: $event,
        ));
    }
}
