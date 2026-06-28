<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\Circular;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Circulars — created here with an optional Media attachment (Media Platform
 * reference, never a path) and sent through the Communication Engine.
 */
class CircularService extends BaseCrudService
{
    public function __construct(private readonly CommunicationEngine $engine) {}

    protected function model(): string
    {
        return Circular::class;
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
        return ['id', 'publish_date', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $data['created_by'] = Auth::id();
            $circular = Circular::query()->create($data);

            $this->engine->publish(new CommunicationRequestData(
                schoolId: (int) $circular->school_id,
                channel: CommunicationChannel::InApp,
                audienceType: $circular->audience_type instanceof AudienceType ? $circular->audience_type : AudienceType::School,
                classId: $circular->class_id,
                sectionId: $circular->section_id,
                departmentId: $circular->department_id,
                subject: $circular->title,
                body: $circular->body,
                source: 'communication',
                event: 'circular',
                createdBy: Auth::id(),
            ));

            return $circular;
        });
    }
}
