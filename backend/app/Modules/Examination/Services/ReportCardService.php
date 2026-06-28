<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamAttendance;
use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Models\ReportCardTemplate;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\Models\Identity;

/**
 * Assembles report-card DATA (no visual designer). The card only ever lists the
 * student's ASSIGNED subjects — subjects the student did not take are never
 * shown. Identity QR comes from the Platform Identity Service (public id only).
 */
class ReportCardService
{
    public function __construct(private readonly ResultProcessingService $results) {}

    /**
     * @return array<string, mixed>
     */
    public function forStudent(int $sessionId, int $studentId): array
    {
        $session = ExamSession::query()->with(['examType:id,name', 'academicYear:id,name', 'term:id,name'])->findOrFail($sessionId);
        $student = Student::query()->findOrFail($studentId);
        $identity = $student->identity_id !== null ? Identity::query()->find($student->identity_id) : null;

        $subjects = $this->results->subjectResults($sessionId, $studentId);
        $result = ExamResult::query()
            ->with('grade:id,code,name,remarks')
            ->where('exam_session_id', $sessionId)
            ->where('student_id', $studentId)
            ->first();

        $template = ReportCardTemplate::query()
            ->where('school_id', $session->school_id)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->first();

        $examAttendance = ExamAttendance::query()
            ->where('school_id', $session->school_id)
            ->whereIn('exam_schedule_id', function ($q) use ($sessionId): void {
                $q->select('id')->from('exam_schedules')->where('exam_session_id', $sessionId);
            })
            ->where('student_id', $studentId)
            ->get();

        return [
            'session' => [
                'id' => $session->id,
                'name' => $session->name,
                'exam_type' => $session->examType?->name,
                'academic_year' => $session->academicYear?->name,
                'term' => $session->term?->name,
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'admission_number' => $student->admission_number,
                'photo_media_id' => $student->photo_media_id,
            ],
            'identity' => $identity !== null ? [
                'identity_number' => $identity->identity_number,
                'public_identifier' => $identity->public_identifier,
                'qr_url' => "/api/v1/identity/{$identity->id}/qr",
            ] : null,
            // Only assigned subjects appear here.
            'subjects' => $subjects,
            'summary' => [
                'total_obtained' => $result?->total_obtained ?? 0,
                'total_max' => $result?->total_max ?? 0,
                'percentage' => $result?->percentage ?? 0,
                'grade' => $result?->grade?->code,
                'gpa' => $result?->gpa,
                'result_status' => $result?->result_status?->value,
                'rank' => $result?->rank,
            ],
            'attendance_summary' => [
                'present' => $examAttendance->where('status', 'present')->count(),
                'absent' => $examAttendance->where('status', 'absent')->count(),
                'total_exams' => $examAttendance->count(),
            ],
            'template' => $template?->config ?? [
                'show_logo' => true,
                'show_photo' => true,
                'show_qr' => true,
                'show_attendance' => true,
                'show_remarks' => true,
                'class_teacher_signature' => 'Class Teacher',
                'principal_signature' => 'Principal',
            ],
        ];
    }
}
