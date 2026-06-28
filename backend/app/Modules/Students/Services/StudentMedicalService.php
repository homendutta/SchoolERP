<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Support\TimelineEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;

/**
 * Student medical information. Blood group is a Master Data reference
 * (blood_group_id), never hardcoded.
 */
class StudentMedicalService extends BaseService
{
    private const FIELDS = [
        'blood_group_id', 'allergies', 'disabilities', 'medical_notes', 'emergency_instructions',
    ];

    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
    ) {}

    /** @param array<string, mixed> $data */
    public function update(Student $student, array $data): Student
    {
        return $this->transaction(function () use ($student, $data): Student {
            $student->fill(array_intersect_key($data, array_flip(self::FIELDS)))->save();

            $this->timeline->record($student, TimelineEvent::MedicalUpdated, 'Medical information updated');
            $this->activity->record('student.medical_updated', "Medical updated for {$student->name}", $student, [], $student->school_id, 'students');

            return $student->refresh();
        });
    }
}
