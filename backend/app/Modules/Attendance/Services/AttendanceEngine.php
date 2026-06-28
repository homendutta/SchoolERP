<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\DTO\AttendanceMarkData;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

/**
 * The reusable Attendance Engine. Manual, Import and Biometric all flow through
 * here, so attendance is recorded identically regardless of source. It resolves
 * the Identity's owner, derives the academic/employment context, prevents
 * duplicates, and writes the audit log + the owner's timeline.
 */
class AttendanceEngine extends BaseService
{
    public function __construct(
        private readonly StudentTimelineService $studentTimeline,
        private readonly StaffTimelineService $staffTimeline,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * Record (or correct) attendance for an Identity. Duplicate protection is on
     * Identity + date + session.
     */
    public function mark(Identity $identity, AttendanceMarkData $data): AttendanceRecord
    {
        return $this->transaction(function () use ($identity, $data): AttendanceRecord {
            $owner = $identity->owner;
            if ($owner === null) {
                throw BusinessRuleException::make('Identity has no owner to attribute attendance to.', 'IDENTITY_NO_OWNER');
            }

            $existing = $this->existing($identity->id, $data->date, $data->sessionId);

            if ($existing !== null) {
                return match ($data->mode) {
                    'skip' => $existing,
                    'correct' => $this->correct($existing, $data, $owner),
                    default => throw BusinessRuleException::make('Attendance already recorded for this date/session.', 'DUPLICATE_ATTENDANCE'),
                };
            }

            $record = AttendanceRecord::create([
                ...$this->ownerContext($owner),
                'identity_id' => $identity->id,
                'owner_type' => $owner::class,
                'owner_id' => $owner->getKey(),
                'session_id' => $data->sessionId,
                'shift' => $data->shift,
                'attendance_date' => $data->date,
                'status' => $data->status->value,
                'source' => $data->source->value,
                'check_in_time' => $data->checkInTime,
                'check_out_time' => $data->checkOutTime,
                'is_late' => $data->isLate || $data->status === AttendanceStatus::Late,
                'remarks' => $data->remarks,
                'biometric_log_id' => $data->biometricLogId,
                'recorded_by' => $data->recordedBy,
            ]);

            $this->writeTimeline($owner, $record, $record->is_late ? 'attendance.late_arrival' : 'attendance.marked',
                $record->is_late ? 'Late arrival' : 'Attendance marked ('.$data->status->label().')');
            $this->activity->record('attendance.marked', "Attendance {$data->status->value} via {$data->source->value}", $record, [
                'status' => $data->status->value, 'source' => $data->source->value,
            ], $record->school_id, 'attendance');

            return $record;
        });
    }

    /** Find an existing record for the Identity/date/session (null-session safe). */
    public function existing(int $identityId, string $date, ?int $sessionId): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->where('identity_id', $identityId)
            ->whereDate('attendance_date', $date)
            ->when($sessionId === null, fn ($q) => $q->whereNull('session_id'), fn ($q) => $q->where('session_id', $sessionId))
            ->first();
    }

    private function correct(AttendanceRecord $record, AttendanceMarkData $data, Model $owner): AttendanceRecord
    {
        $record->forceFill([
            'status' => $data->status->value,
            'source' => $data->source->value,
            'is_late' => $data->isLate || $data->status === AttendanceStatus::Late,
            'remarks' => $data->remarks ?? $record->remarks,
            'check_out_time' => $data->checkOutTime ?? $record->check_out_time,
            'recorded_by' => $data->recordedBy ?? $record->recorded_by,
        ])->save();

        $this->writeTimeline($owner, $record, 'attendance.corrected', 'Attendance corrected to '.$data->status->label());
        $this->activity->record('attendance.corrected', "Attendance corrected to {$data->status->value}", $record, [
            'status' => $data->status->value,
        ], $record->school_id, 'attendance');

        return $record->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function ownerContext(Model $owner): array
    {
        if ($owner instanceof Student) {
            $current = $owner->currentRecord()->first();

            return [
                'school_id' => $owner->school_id,
                'academic_year_id' => $current?->academic_year_id,
                'class_id' => $current?->class_id,
                'section_id' => $current?->section_id,
            ];
        }

        if ($owner instanceof Staff) {
            return [
                'school_id' => $owner->school_id,
                'department_id' => $owner->department_id,
                'designation_id' => $owner->designation_id,
            ];
        }

        return ['school_id' => $owner->getAttribute('school_id')];
    }

    private function writeTimeline(Model $owner, AttendanceRecord $record, string $event, string $title): void
    {
        if ($owner instanceof Student) {
            $this->studentTimeline->record($owner, $event, $title, null, ['attendance_id' => $record->id]);
        } elseif ($owner instanceof Staff) {
            $this->staffTimeline->record($owner, $event, $title, null, ['attendance_id' => $record->id]);
        }
    }
}
