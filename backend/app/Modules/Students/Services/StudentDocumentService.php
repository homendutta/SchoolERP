<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Models\StudentDocument;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Student documents — references the Media Upload Pipeline by media_id only,
 * never raw paths. Document type comes from Master Data.
 */
class StudentDocumentService extends BaseCrudService
{
    public function __construct(private readonly ActivityLogger $activity, private readonly StudentTimelineService $timeline) {}

    protected function model(): string
    {
        return StudentDocument::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['documentType:id,label,value', 'media:id,uuid,stored_filename,disk,visibility']);
    }

    protected function filterable(): array
    {
        return ['student_id', 'document_type_id', 'status', 'school_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        /** @var StudentDocument $document */
        $document = parent::create($data);

        $this->timeline->record((int) $document->student_id, TimelineEvent::DocumentAdded, $document->title ?? 'Document added');
        $this->activity->record('student.document_added', 'Document added', $document, [], $document->school_id, 'students');

        return $document;
    }
}
