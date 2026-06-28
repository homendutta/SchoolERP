<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Modules\Staff\Models\StaffDocument;
use App\Modules\Staff\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Staff documents — references the Media Upload Pipeline by media_id only, never
 * raw paths. Document type comes from Master Data.
 */
class StaffDocumentService extends BaseCrudService
{
    public function __construct(private readonly ActivityLogger $activity, private readonly StaffTimelineService $timeline) {}

    protected function model(): string
    {
        return StaffDocument::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['documentType:id,label,value', 'media:id,uuid,stored_filename,disk,visibility']);
    }

    protected function filterable(): array
    {
        return ['staff_id', 'document_type_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        /** @var StaffDocument $document */
        $document = parent::create($data);

        $this->timeline->record((int) $document->staff_id, TimelineEvent::DocumentAdded, $document->title ?? 'Document added');
        $this->activity->record('staff.document_added', 'Document added', $document, [], $document->school_id, 'staff');

        return $document;
    }
}
