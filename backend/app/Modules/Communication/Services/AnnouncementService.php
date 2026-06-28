<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\Announcement;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Announcements — created here and sent through the Communication Engine (the
 * announcement is also an in-app message to its audience).
 */
class AnnouncementService extends BaseCrudService
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    protected function model(): string
    {
        return Announcement::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['schoolClass:id,name', 'section:id,name']);
    }

    protected function searchable(): array
    {
        return ['title', 'body'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'audience_type', 'class_id', 'section_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'published_at', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $data['published_at'] ??= now();
            $data['created_by'] = Auth::id();
            $announcement = Announcement::query()->create($data);

            // Send through the engine (announcements never bypass it).
            $this->engine->publish(new CommunicationRequestData(
                schoolId: (int) $announcement->school_id,
                channel: CommunicationChannel::InApp,
                audienceType: $announcement->audience_type instanceof AudienceType ? $announcement->audience_type : AudienceType::School,
                classId: $announcement->class_id,
                sectionId: $announcement->section_id,
                departmentId: $announcement->department_id,
                subject: $announcement->title,
                body: $announcement->body,
                source: 'communication',
                event: 'announcement',
                createdBy: Auth::id(),
            ));

            return $announcement;
        });
    }
}
