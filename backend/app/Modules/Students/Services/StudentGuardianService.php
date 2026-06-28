<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;

/**
 * Manages the Student ↔ Guardian relationship, which lives entirely on the
 * pivot. A student may have many guardians and a guardian many students, but
 * only ONE primary guardian per student. Relationship type is Master Data.
 */
class StudentGuardianService extends BaseService
{
    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * Link a guardian to a student (or update the link if it already exists),
     * enforcing the single-primary rule.
     *
     * @param  array<string, mixed>  $pivot
     */
    public function link(Student $student, int $guardianId, array $pivot): Student
    {
        return $this->transaction(function () use ($student, $guardianId, $pivot): Student {
            $data = $this->pivotData($pivot);

            if (! empty($data['is_primary'])) {
                $this->clearPrimary($student);
            }

            $student->guardians()->syncWithoutDetaching([$guardianId => $data]);
            // syncWithoutDetaching does not update an existing row's pivot, so set it explicitly.
            $student->guardians()->updateExistingPivot($guardianId, $data);

            $this->timeline->record($student, TimelineEvent::ProfileUpdated, 'Guardian linked/updated');
            $this->activity->record('student.guardian_linked', "Guardian linked to {$student->name}", $student, [
                'guardian_id' => $guardianId,
            ], $student->school_id, 'students');

            return $student->refresh();
        });
    }

    public function unlink(Student $student, int $guardianId): Student
    {
        $student->guardians()->detach($guardianId);
        $this->activity->record('student.guardian_unlinked', "Guardian unlinked from {$student->name}", $student, [
            'guardian_id' => $guardianId,
        ], $student->school_id, 'students');

        return $student->refresh();
    }

    /**
     * Ensure only one primary guardian survives for a student.
     */
    private function clearPrimary(Student $student): void
    {
        $primaryIds = $student->guardians()->wherePivot('is_primary', true)->pluck('guardians.id')->all();
        foreach ($primaryIds as $id) {
            $student->guardians()->updateExistingPivot($id, ['is_primary' => false]);
        }
    }

    /**
     * @param  array<string, mixed>  $pivot
     * @return array<string, mixed>
     */
    private function pivotData(array $pivot): array
    {
        $allowed = ['relationship_type_id', 'is_primary', 'emergency_contact', 'pickup_authorized', 'financial_responsible', 'notes'];
        $data = array_intersect_key($pivot, array_flip($allowed));

        if ($data === []) {
            throw BusinessRuleException::make('No relationship details provided.', 'GUARDIAN_LINK_EMPTY');
        }

        return $data;
    }
}
