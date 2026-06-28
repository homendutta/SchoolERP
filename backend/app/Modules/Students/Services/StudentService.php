<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Enums\StudentStatus;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Student lifecycle service. Students are never created here (Admissions /
 * import own creation) — this maintains the student: profile, search, and
 * profile updates with a timeline + audit trail.
 */
class StudentService extends BaseCrudService
{
    /** @var array<string, mixed> */
    private array $listParams = [];

    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    protected function model(): string
    {
        return Student::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'currentRecord.schoolClass:id,name',
            'currentRecord.section:id,name',
            'currentRecord.academicYear:id,name',
            'guardians:id,name,phone,parent_number',
            'bloodGroup:id,label,value',
        ]);
    }

    protected function searchable(): array
    {
        return ['name', 'admission_number'];
    }

    protected function filterable(): array
    {
        return ['status', 'school_id', 'gender'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'admission_number', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'admission_number' => ['type' => 'text', 'columns' => ['admission_number']],
            'name' => ['type' => 'text', 'columns' => ['name']],
            'status' => ['type' => 'enum', 'enum' => StudentStatus::class],
            'guardian' => ['type' => 'relation', 'relation' => 'guardians', 'columns' => ['name', 'phone']],
        ];
    }

    /**
     * Enterprise student search. Beyond own columns it scopes by the CURRENT
     * academic placement (class / section / academic year).
     *
     * @param  array<string, mixed>  $params
     */
    public function list(array $params): LengthAwarePaginator
    {
        $this->listParams = $params;

        return parent::list($params);
    }

    protected function query(): Builder
    {
        $query = parent::query();
        $filter = (array) ($this->listParams['filter'] ?? []);

        foreach (['class_id', 'section_id', 'academic_year_id'] as $column) {
            if (! empty($filter[$column])) {
                $value = $filter[$column];
                $query->whereHas('currentRecord', fn (Builder $r) => $r->where($column, $value));
            }
        }

        return $query;
    }

    /** Update the student profile and record it on the timeline + audit log. */
    public function updateProfile(Student $student, array $data): Student
    {
        /** @var Student $updated */
        $updated = $this->update($student, $data);

        $this->timeline->record($updated, TimelineEvent::ProfileUpdated, 'Profile updated');
        $this->activity->record('student.profile_updated', "Updated {$updated->name}", $updated, [], $updated->school_id, 'students');

        return $updated;
    }

    /** Convenience: full profile for the show endpoint. */
    public function profile(int|string $id): Model
    {
        return Student::query()
            ->with([
                'currentRecord.schoolClass:id,name',
                'currentRecord.section:id,name',
                'currentRecord.academicYear:id,name',
                'academicRecords.schoolClass:id,name',
                'academicRecords.section:id,name',
                'academicRecords.academicYear:id,name',
                'guardians',
                'bloodGroup:id,label,value',
                'documents.documentType:id,label,value',
            ])
            ->findOrFail($id);
    }
}
