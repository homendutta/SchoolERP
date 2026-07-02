<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Modules\Transport\Enums\AssignmentStatus;
use App\Modules\Transport\Models\Stop;
use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Models\TransportRoute;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * Assigns a student to a ROUTE + STOP (never directly to a vehicle). Enforces
 * capacity, preserves history (a re-assignment supersedes the previous active
 * one), writes the student Timeline + Audit Log, and publishes a route-changed
 * communication event.
 */
class StudentAssignmentEngine extends BaseService
{
    public function __construct(
        private readonly CapacityService $capacity,
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
        private readonly TransportHooks $hooks,
    ) {}

    /**
     * @param  array{academic_year_id?:int|null, shift?:string|null}  $options
     */
    public function assign(int $studentId, int $routeId, int $stopId, array $options = []): StudentAssignment
    {
        return $this->transaction(function () use ($studentId, $routeId, $stopId, $options): StudentAssignment {
            $student = Student::query()->findOrFail($studentId);
            $stop = Stop::query()->findOrFail($stopId);

            if ((int) $stop->route_id !== $routeId) {
                throw BusinessRuleException::make('The stop does not belong to the selected route.', 'STOP_ROUTE_MISMATCH');
            }

            $this->capacity->assertCanAssign($routeId, $stop, $studentId);

            // Supersede any current active assignment (history preserved).
            StudentAssignment::query()
                ->where('student_id', $studentId)
                ->where('status', AssignmentStatus::Active->value)
                ->update(['status' => AssignmentStatus::Transferred->value, 'ended_on' => Carbon::now()->toDateString()]);

            $assignment = StudentAssignment::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $studentId,
                'route_id' => $routeId,
                'stop_id' => $stopId,
                'academic_year_id' => $options['academic_year_id'] ?? null,
                'shift' => $options['shift'] ?? null,
                'status' => AssignmentStatus::Active->value,
                'assigned_on' => Carbon::now()->toDateString(),
            ]);

            $route = TransportRoute::query()->find($routeId);
            $this->timeline->record($student, 'transport.assigned', 'Assigned to transport '.($route?->name ?? ''), null, [
                'route_id' => $routeId, 'stop_id' => $stopId,
            ]);
            $this->activity->record('transport.student_assigned', "Student assigned to route {$routeId}", $assignment, [
                'route_id' => $routeId, 'stop_id' => $stopId,
            ], (int) $student->school_id, 'transport');

            $this->hooks->routeChanged((int) $student->school_id, $student, (string) ($route?->name ?? ''));

            return $assignment->refresh();
        });
    }

    public function cancel(StudentAssignment $assignment): StudentAssignment
    {
        $assignment->update(['status' => AssignmentStatus::Cancelled->value, 'ended_on' => Carbon::now()->toDateString()]);
        $this->activity->record('transport.assignment_cancelled', 'Transport assignment cancelled', $assignment, [], (int) $assignment->school_id, 'transport');

        return $assignment->refresh();
    }
}
