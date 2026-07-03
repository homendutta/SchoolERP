<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Cms\Services\PublicContentService;
use App\Modules\Communication\Models\Announcement;
use App\Modules\Communication\Models\Circular;
use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Services\ReportCardService;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Services\DueTrackingService;
use App\Modules\Hostel\Models\Allocation;
use App\Modules\Library\Models\Borrowing;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Modules\Timetable\Services\DerivedTimetableService;
use App\Modules\Transport\Models\StudentAssignment;

/**
 * Read-only aggregation over the existing ERP modules for the portals. It owns NO
 * business logic — every figure is read from (or delegated to) the module that
 * owns it (Attendance, Finance, Examination, Library, Transport, Hostel,
 * Communication, Timetable, CMS). It only projects that data for a given student.
 */
class PortalDataService
{
    public function __construct(
        private readonly DueTrackingService $dues,
        private readonly ReportCardService $reportCards,
        private readonly DerivedTimetableService $timetable,
        private readonly PublicContentService $cms,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function attendance(int $studentId, int $limit = 60): array
    {
        $records = AttendanceRecord::query()
            ->where('owner_type', Student::class)->where('owner_id', $studentId)
            ->orderByDesc('attendance_date')->limit($limit)->get(['attendance_date', 'status']);

        $count = fn (AttendanceStatus $s): int => $records->filter(fn ($r) => $r->status === $s)->count();
        $present = $count(AttendanceStatus::Present) + $count(AttendanceStatus::Late) + $count(AttendanceStatus::HalfDay);
        $total = $records->count();

        return [
            'summary' => [
                'total' => $total,
                'present' => $present,
                'absent' => $count(AttendanceStatus::Absent),
                'leave' => $count(AttendanceStatus::Leave),
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
            ],
            'recent' => $records->take(30)->map(fn ($r) => [
                'date' => $r->attendance_date?->toDateString(),
                'status' => $r->status->value,
            ])->values()->all(),
        ];
    }

    /**
     * Outstanding fees — delegated entirely to Finance (source of truth).
     *
     * @return array<string, mixed>
     */
    public function fees(int $studentId): array
    {
        return $this->dues->forStudent($studentId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function feeHistory(int $studentId): array
    {
        return Payment::query()->where('student_id', $studentId)
            ->orderByDesc('paid_on')->orderByDesc('id')->limit(200)->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'receipt_number' => $p->receipt_number,
                'amount' => (float) $p->amount,
                'paid_on' => $p->paid_on?->toDateString(),
                'gateway' => $p->gateway,
                'status' => $p->status->value,
            ])->all();
    }

    /**
     * Report card / results — delegated to the Examination module.
     *
     * @return array<string, mixed>
     */
    public function examinations(int $studentId, ?int $sessionId): array
    {
        if ($sessionId !== null) {
            return $this->reportCards->forStudent($sessionId, $studentId);
        }

        // No session specified — list the sessions this student has results for.
        $sessions = ExamResult::query()->where('student_id', $studentId)
            ->with('session:id,name')->get()
            ->groupBy('exam_session_id')
            ->map(fn ($group) => [
                'session_id' => (int) $group->first()->exam_session_id,
                'name' => $group->first()->session?->name,
            ])->values()->all();

        return ['sessions' => $sessions];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function library(int $studentId): array
    {
        return Borrowing::query()
            ->where('owner_type', Student::class)->where('owner_id', $studentId)
            ->orderByDesc('borrow_date')->limit(100)->get()
            ->map(fn (Borrowing $b) => [
                'id' => $b->id,
                'book_id' => $b->book_id,
                'borrow_date' => $b->borrow_date?->toDateString(),
                'due_date' => $b->due_date?->toDateString(),
                'return_date' => $b->return_date?->toDateString(),
                'status' => $b->status->value,
                'fine_amount' => (float) $b->fine_amount,
            ])->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function transport(int $studentId): ?array
    {
        $assignment = StudentAssignment::query()->where('student_id', $studentId)
            ->with(['route:id,name,code', 'stop:id,name'])->latest('id')->first();
        if ($assignment === null) {
            return null;
        }

        return [
            'route' => $assignment->route?->name,
            'route_code' => $assignment->route?->code,
            'stop' => $assignment->stop?->name,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function hostel(int $studentId): ?array
    {
        $allocation = Allocation::query()->where('student_id', $studentId)
            ->where('status', 'active')->with(['room:id,name', 'bed:id,name'])->latest('id')->first();
        if ($allocation === null) {
            return null;
        }

        return [
            'hostel_id' => $allocation->hostel_id,
            'room' => $allocation->room?->name,
            'bed' => $allocation->bed?->name,
            'allocation_date' => $allocation->allocation_date?->toDateString(),
        ];
    }

    /**
     * The student's current-year class timetable (derived by the Timetable module).
     *
     * @return array<int, mixed>
     */
    public function studentTimetable(int $studentId): array
    {
        $record = StudentAcademicRecord::query()->where('student_id', $studentId)
            ->where('is_current', true)->latest('id')->first();
        if ($record === null) {
            return [];
        }

        return $this->timetable->forClass((int) $record->class_id, $record->section_id, (int) $record->academic_year_id)->all();
    }

    /**
     * A teacher's derived timetable.
     *
     * @return array<int, mixed>
     */
    public function teacherTimetable(int $staffId, int $academicYearId): array
    {
        return $this->timetable->forTeacher($staffId, $academicYearId)->all();
    }

    /**
     * Broadcast inbox — announcements + circulars from the Communication module.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function messages(int $schoolId): array
    {
        $announcements = Announcement::query()->where('school_id', $schoolId)->where('status', 'published')
            ->latest('published_at')->limit(30)->get()
            ->map(fn (Announcement $a) => [
                'id' => $a->id, 'title' => $a->title, 'body' => $a->body,
                'published_at' => $a->published_at?->toDateTimeString(),
            ])->all();

        $circulars = Circular::query()->where('school_id', $schoolId)->where('status', 'published')
            ->latest('publish_date')->limit(30)->get()
            ->map(fn (Circular $c) => [
                'id' => $c->id, 'title' => $c->title, 'body' => $c->body,
                'attachment' => $c->media_id !== null ? $this->cms->url((int) $c->media_id) : null,
                'publish_date' => $c->publish_date?->toDateString(),
            ])->all();

        return ['announcements' => $announcements, 'circulars' => $circulars];
    }

    /**
     * Downloads — delegated to the Website CMS module.
     *
     * @return array<int, array<string, mixed>>
     */
    public function downloads(int $schoolId): array
    {
        return $this->cms->downloads($schoolId);
    }
}
