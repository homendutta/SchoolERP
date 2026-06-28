<?php

declare(strict_types=1);

namespace App\Modules\Students\Services;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentTimeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The reusable Student Timeline. Any module records important student events here
 * and reads them back newest-first.
 */
class StudentTimelineService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        Student|int $student,
        string $eventType,
        string $title,
        ?string $description = null,
        array $metadata = [],
    ): StudentTimeline {
        return StudentTimeline::create([
            'student_id' => $student instanceof Student ? $student->id : $student,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'performed_by' => Auth::id(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    /** Timeline for a student, newest first. */
    public function forStudent(int $studentId): Collection
    {
        return StudentTimeline::query()
            ->where('student_id', $studentId)
            ->latest('id')
            ->get();
    }
}
